<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
     header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: report_form.php");
    exit();
}

// Lấy và xử lý dữ liệu
$reporter_name = $_POST['reporter_name'] ?? '';
$reporter_email = $_POST['reporter_email'] ?? '';
$report_type = $_POST['report_type'] ?? 'voluntary';
$location = $_POST['location'] ?? '';
$location_other = ($location === 'Mục khác') ? ($_POST['location_other'] ?? '') : '';
$incident_group = $_POST['incident_group'] ?? '';
$incident_date = $_POST['incident_date'] ?? '';
$affected_subject = $_POST['affected_subject'] ?? '';
$affected_subject_other = ($affected_subject === 'Mục khác') ? ($_POST['affected_subject_other'] ?? '') : '';
$patient_info = $_POST['patient_info'] ?? '';
$incident_description = $_POST['incident_description'] ?? '';
$incident_nature = $_POST['incident_nature'] ?? '';
$severity_level = $_POST['severity_level'] ?? '';
$incident_category = $_POST['incident_category'] ?? '';
$reported_to_superior = $_POST['reported_to_superior'] ?? '';
$initial_treatment = $_POST['initial_treatment'] ?? '';
$preventive_solution = $_POST['preventive_solution'] ?? '';
$documented_in_record = $_POST['documented_in_record'] ?? 'no';

// Xử lý location
if ($location === 'Mục khác' && !empty($location_other)) {
    $location = $location_other;
}

// Xử lý affected_subject
if ($affected_subject === 'Mục khác' && !empty($affected_subject_other)) {
    $affected_subject = $affected_subject_other;
}

// Insert vào database
$sql = "INSERT INTO medical_reports (
    reporter_name, reporter_email, report_type, location, incident_group,
    incident_date, affected_subject, patient_info, incident_description,
    incident_nature, severity_level, incident_category, reported_to_superior,
    initial_treatment, preventive_solution, documented_in_record
) VALUES (
    :reporter_name, :reporter_email, :report_type, :location, :incident_group,
    :incident_date, :affected_subject, :patient_info, :incident_description,
    :incident_nature, :severity_level, :incident_category, :reported_to_superior,
    :initial_treatment, :preventive_solution, :documented_in_record
)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':reporter_name' => $reporter_name,
    ':reporter_email' => $reporter_email,
    ':report_type' => $report_type,
    ':location' => $location,
    ':incident_group' => $incident_group,
    ':incident_date' => $incident_date,
    ':affected_subject' => $affected_subject,
    ':patient_info' => $patient_info,
    ':incident_description' => $incident_description,
    ':incident_nature' => $incident_nature,
    ':severity_level' => $severity_level,
    ':incident_category' => $incident_category,
    ':reported_to_superior' => $reported_to_superior,
    ':initial_treatment' => $initial_treatment,
    ':preventive_solution' => $preventive_solution,
    ':documented_in_record' => $documented_in_record
]);

$report_id = $pdo->lastInsertId();

$_SESSION['success'] = "Báo cáo #$report_id đã được gửi thành công!";
header("Location: view_detail.php?id=$report_id");
exit();
?>