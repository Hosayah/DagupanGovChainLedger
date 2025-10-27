<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['next'])) {
        if ($_POST['next'] === 'inc') {
            $_SESSION['limit'] += 5;
        } elseif ($_POST['next'] === 'dec' && $_SESSION['limit'] >= 5) {
            $_SESSION['limit'] -= 5;
        }
    }
}