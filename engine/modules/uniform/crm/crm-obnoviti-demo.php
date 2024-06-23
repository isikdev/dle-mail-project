use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Условное подключение классов PHPMailer
if (!class_exists('PHPMailer\PHPMailer\Exception')) {
    require_once '/var/www/p547388/data/www/alto-group.com/engine/modules/uniform/crm/src/Exception.php';
}
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once '/var/www/p547388/data/www/alto-group.com/engine/modules/uniform/crm/src/PHPMailer.php';
}
if (!class_exists('PHPMailer\PHPMailer\SMTP')) {
    require_once '/var/www/p547388/data/www/alto-group.com/engine/modules/uniform/crm/src/SMTP.php';
}

$mail = new PHPMailer;

// Настройки PHPMailer
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

$mail->isSMTP();
$mail->Host = "mail.alto-group.ru";
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Port = 465;
$mail->CharSet = "UTF-8";
$mail->Username = "test@alto-group.ru";
$mail->Password = "1234567890a";

$mail->setFrom("info@alto-group.ru", "Alto Consulting Group (ACG)");
$mail->Subject = "Демо-версия исследования - Alto Consulting Group (ACG)";
$mail->msgHTML("<html><body>Добрый день!<br /><br />По запросу отправляем демо-версию исследования: alto-group.ru</body></html>");
$mail->addAddress($email_dm, "Alto Consulting Group (ACG)");

$mail->send();
