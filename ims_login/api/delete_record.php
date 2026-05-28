<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/connect.php';

$allowed = [
    'items' => ['id_col' => 'item_id'],
    'customers' => ['id_col' => 'id'],
    'suppliers' => ['id_col' => 'id'],
    'sales' => ['id_col' => 'item_id']
];

// capture inputs and log for debugging
$table = $_POST['table'] ?? '';
$id = $_POST['id'] ?? null;
function dbg_log_del($msg) {
    $logfile = __DIR__ . '/../debug_delete.log';
    file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}
dbg_log_del('Received POST: ' . json_encode($_POST));
if (!$table || !$id || !isset($allowed[$table])) {
    echo json_encode(['success'=>false,'error'=>'Invalid parameters']);
    exit;
}

$id_col = $allowed[$table]['id_col'];
$sql = "DELETE FROM `$table` WHERE `$id_col` = ?";
dbg_log_del('Prepared SQL: ' . $sql . ' Params: ' . json_encode([$id]));

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    dbg_log_del('Prepare failed: ' . $conn->error);
    echo json_encode(['success'=>false,'error'=>$conn->error]);
    exit;
}

$stmt->bind_param('s', $id);
$clientDebug = isset($_POST['debug']) && $_POST['debug'] == '1';
if ($stmt->execute()) {
    dbg_log_del('Execute OK. Affected rows: ' . $stmt->affected_rows);
    $resp = ['success'=>true];
    if ($clientDebug) $resp['_debug'] = ['sql'=>$sql,'params'=>[$id],'affected'=>$stmt->affected_rows];
    echo json_encode($resp);
} else {
    dbg_log_del('Execute failed: ' . $stmt->error);
    $resp = ['success'=>false,'error'=>$stmt->error];
    if ($clientDebug) $resp['_debug'] = ['sql'=>$sql,'params'=>[$id]];
    echo json_encode($resp);
}

$stmt->close();
$conn->close();

?>

