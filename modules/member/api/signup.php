<?php
$HOME_DIR = str_replace(basename(__FILE__), '', realpath(__FILE__));
require_once($HOME_DIR . '../../_configure/dbconn.php');
require_once($HOME_DIR . '../../_configure/recaptcha.php');
header('Content-type: application/json;');
?>
<?php
//exit;
$response_array['title'] = 'Inspirare JSON Response[singup.php]';
if(!isset($_SESSION))
{
    session_start();
}
if (isset($_POST["user_email"]) && isset($_POST["user_pw"]) && isset($_POST["user_nick"])) {
    $user_email = $_POST["user_email"];
    $user_pw = password_hash($_POST["user_pw"], PASSWORD_DEFAULT, ["cost" => 12]);
    $user_nick = $_POST["user_nick"];

    $data =
        'secret=' .$recaptcha_secretKey.
        '&response='. $_POST['g-recaptcha-response'].
        '&remoteip='. $_SERVER['REMOTE_ADDR'];


    $url = "https://www.google.com/recaptcha/api/siteverify";

    $ch = curl_init();                                               //curl 초기화
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/x-www-form-urlencoded',
        'Content-length: '. strlen($data)
    ));
    curl_setopt($ch, CURLOPT_URL, $url);                      //URL 지정하기
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    //요청 결과를 문자열로 반환
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);      //connection timeout 10초
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //원격 서버의 인증서가 유효한지 검사 안함
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);              //POST data
    curl_setopt($ch, CURLOPT_POST, true);              //true시 post 전송

    $response = curl_exec($ch);
    $responseKeys = json_decode($response, true);
    $response_array['sadfasdf'] = $response;
    curl_close($ch);
    if ($responseKeys["success"]) {

        $sql = "INSERT INTO `pir_members` (
                    `member_email`,
                    `member_pw`,
                    `member_nickname`
                ) VALUES (
                    '$user_email',
                    '$user_pw',
                    '$user_nick'
                );
            ";
        $rs = mysqli_query($dbconn, $sql);

        if ($rs === true) {
            $response_array['status'] = true;
            $response_array['msg'] = '회원가입 성공';
        } else {
            $response_array['status'] = false;
            $response_array['msg'] = '회원가입 실패';
            $response_array['error']['msg'] = mysqli_error($dbconn);
            $response_array['error']['code'] = mysqli_errno($dbconn);

        }
    } else {
        $response_array['status'] = false;
        $response_array['msg'] = '정상적인 경로가 아닙니다.';
    }
} else {
    $response_array['status'] = false;
    $response_array['msg'] = '입력 값이 유효하지 않습니다.';
}
echo json_encode($response_array);
?>
