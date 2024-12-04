function send_request_email($user_email, $user_name, $user_data) {
    $to = $user_email;
    $subject = "3D Printing Request Confirmation";
    $message = "Thank you for your request, " . $user_name . ". Here is the summary: \n" . $user_data;

    // Gửi email cho người dùng
    wp_mail($to, $subject, $message);

    // Gửi email đến admin
    $admin_email = "lysimjanuar@gmail.com";
    wp_mail($admin_email, $subject, $message);
}
