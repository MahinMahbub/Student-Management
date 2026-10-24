<?php
/**
 * includes/flash.php
 * Tiny helper for setting one-time flash messages shown after a redirect.
 */

function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}
