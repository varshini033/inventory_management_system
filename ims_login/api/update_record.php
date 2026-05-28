<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/connect.php';

$allowed = [
    'items' => ['id_col' => 'item_id', 'cols' => ['name','category','wholesale_price','retail_price','quantity']],
    'customers' => ['id_col' => 'id', 'cols' => ['firstName','lastName','email','phone','total_spent']],
    'suppliers' => ['id_col' => 'id', 'cols' => ['company_name','first_name','last_name','email','phone']],
    'sales' => ['id_col' => 'item_id', 'cols' => ['item_name','price','quantity','discount']]
];

// capture raw POST for debugging
$table = $_POST['table'] ?? '';
$id = $_POST['id'] ?? null; // original id (used in WHERE)
$newId = $_POST['new_id'] ?? null; // optional new id value to set for the id column
// debug log helper
function dbg_log($msg) {
    $logfile = __DIR__ . '/../debug_update.log';
    file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

dbg_log('Received POST: ' . json_encode($_POST));
if (!$table || !$id || !isset($allowed[$table])) {
    echo json_encode(['success'=>false,'error'=>'Invalid parameters']);
    exit;
}

$map = $allowed[$table];
$id_col = $map['id_col'];

// Build a lookup of allowed columns keyed by normalized names to support header->column mapping
$allowedCols = [];
foreach ($map['cols'] as $col) {
    // normalized: lowercase and replace non-alphanum with underscore
    $norm = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $col));
    $allowedCols[$norm] = $col;
    // also include camelCase key normalized to snake as fallback
    $camelNorm = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $col));
    $camelNorm = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $camelNorm));
    $allowedCols[$camelNorm] = $col;
}

$updates = [];
$params = [];

// If client asked to change the id column, add it first to the update list
if ($newId !== null && $newId !== '') {
    $updates[] = "`$id_col` = ?";
    $params[] = (string)$newId;
}

foreach ($_POST as $key => $value) {
    if (in_array($key, ['table','id','new_id'])) continue;
    $normKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $key));
    if (isset($allowedCols[$normKey])) {
        $colName = $allowedCols[$normKey];
        $updates[] = "`$colName` = ?";
        $params[] = $value;
    }
}

if (count($updates) === 0) {
    echo json_encode(['success'=>false,'error'=>'No fields to update']);
    exit;
}

// build the SQL
$sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$id_col` = ?";
// original id should be used in the WHERE clause
$params[] = (string)$id;
dbg_log('Prepared SQL: ' . $sql . ' Params: ' . json_encode($params));
$clientDebug = isset($_POST['debug']) && $_POST['debug'] == '1';

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    dbg_log('Prepare failed: ' . $conn->error);
    echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

// bind parameters correctly (bind_param requires references)
$types = str_repeat('s', count($params));
// ensure params are strings
foreach ($params as $i => $p) $params[$i] = (string)$p;
$bindParams = array_merge([$types], $params);
// create array of references
$refs = [];
foreach ($bindParams as $key => $value) {
    $refs[$key] = &$bindParams[$key];
}
if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
    dbg_log('bind_param failed. Types: ' . $types . ' Refs: ' . json_encode($bindParams));
    echo json_encode(['success'=>false,'error'=>'bind_param failed']);
    exit;
}

if ($stmt->execute()) {
    dbg_log('Execute OK. Affected rows: ' . $stmt->affected_rows);
    $resp = ['success'=>true];
    if ($clientDebug) $resp['_debug'] = ['sql'=>$sql,'params'=>$params,'affected'=>$stmt->affected_rows];
    echo json_encode($resp);
} else {
    dbg_log('Execute failed: ' . $stmt->error);
    $resp = ['success'=>false,'error'=>$stmt->error];
    if ($clientDebug) $resp['_debug'] = ['sql'=>$sql,'params'=>$params];
    echo json_encode($resp);
}

$stmt->close();
$conn->close();

?>

