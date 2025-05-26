<?php
// Fungsi untuk mencegah SQL injection
function clean_input($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi untuk redirect dengan pesan
function redirect($location, $message = null)
{
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header("Location: $location");
    exit();
}

// Fungsi untuk menampilkan pesan
function display_message()
{
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
    }
}
?>