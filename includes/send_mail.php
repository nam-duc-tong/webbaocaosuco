<?php
// includes/send_mail.php
use PHPMailer\PHPMailer\PHPMailer;
require_once __DIR__ . '/../config/mail_config.php';
// require_once(__DIR__ . '/includes/send_mail.php');

function sendConfirmationEmail($to_email, $to_name, $report_id, $report_data) {
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Người gửi
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        
        // Người nhận (email người báo cáo)
        $mail->addAddress($to_email, $to_name);
        
        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = '✅ XÁC NHẬN BÁO CÁO SỰ CỐ #' . str_pad($report_id, 5, '0', STR_PAD_LEFT);
        
        // Tạo link tra cứu
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $tracking_url = $protocol . $host . '/track_report.php?id=' . $report_id;
        
        // Nội dung HTML email
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 15px; text-align: center; }
                .content { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                .info-row { margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #ddd; }
                .label { font-weight: bold; display: inline-block; width: 150px; }
                .value { display: inline-block; }
                .footer { text-align: center; padding: 15px; font-size: 12px; color: #666; }
                .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>✅ XÁC NHẬN BÁO CÁO SỰ CỐ</h2>
                    <p>Cảm ơn bạn đã gửi báo cáo!</p>
                </div>
                
                <div class="content">
                    <h3>📋 THÔNG TIN BÁO CÁO CỦA BẠN</h3>
                    
                    <div class="info-row">
                        <span class="label">Mã báo cáo:</span>
                        <span class="value"><strong>#' . str_pad($report_id, 5, '0', STR_PAD_LEFT) . '</strong></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Người báo cáo:</span>
                        <span class="value">' . htmlspecialchars($to_name) . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Thời gian gửi:</span>
                        <span class="value">' . date('d/m/Y H:i:s') . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Địa điểm:</span>
                        <span class="value">' . htmlspecialchars($report_data['diem_xay_ra_su_co'] ?? 'Không có') . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Nhóm sự cố:</span>
                        <span class="value">' . htmlspecialchars($report_data['nhom_su_co'] ?? 'Không có') . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Mức độ:</span>
                        <span class="value">' . htmlspecialchars($report_data['muc_do_su_co'] ?? 'Chưa xác định') . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Phân loại:</span>
                        <span class="value">' . htmlspecialchars($report_data['phan_loai_su_co'] ?? 'Không có') . '</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Mô tả:</span>
                        <div class="value" style="margin-top: 5px;">' . nl2br(htmlspecialchars(substr($report_data['mo_ta_su_co'] ?? '', 0, 200))) . '</div>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="' . $tracking_url . '" class="btn">🔍 TRA CỨU TRẠNG THÁI BÁO CÁO</a>
                    </div>
                    
                    <div style="margin-top: 20px; background: #e7f3ff; padding: 10px; border-radius: 5px;">
                        <p>📌 <strong>HƯỚNG DẪN:</strong> Sử dụng mã báo cáo <strong>#' . str_pad($report_id, 5, '0', STR_PAD_LEFT) . '</strong> để tra cứu tình trạng xử lý.</p>
                        <p>⏳ Thời gian xử lý dự kiến: 24-48 giờ làm việc.</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Email được gửi tự động từ Hệ thống báo cáo sự cố.</p>
                    <p>Mọi thắc mắc vui lòng liên hệ hotline: 1900xxxx</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Nội dung text (cho email client không hỗ trợ HTML)
        $text_body = "XÁC NHẬN BÁO CÁO SỰ CỐ\n";
        $text_body .= "========================\n\n";
        $text_body .= "Cảm ơn bạn đã gửi báo cáo!\n\n";
        $text_body .= "Mã báo cáo: #" . str_pad($report_id, 5, '0', STR_PAD_LEFT) . "\n";
        $text_body .= "Người báo cáo: " . $to_name . "\n";
        $text_body .= "Thời gian gửi: " . date('d/m/Y H:i:s') . "\n";
        $text_body .= "Địa điểm: " . ($report_data['diem_xay_ra_su_co'] ?? 'Không có') . "\n";
        $text_body .= "Mức độ: " . ($report_data['muc_do_su_co'] ?? 'Chưa xác định') . "\n\n";
        $text_body .= "Tra cứu trạng thái tại: " . $tracking_url . "\n";
        
        $mail->Body = $body;
        $mail->AltBody = $text_body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email không gửi được: " . $mail->ErrorInfo);
        return false;
    }
}
?>