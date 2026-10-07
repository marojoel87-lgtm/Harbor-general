<?php
/**
 * Harbor General - Authentication Helpers
 * Handles two separate kinds of login: staff (dashboard) and
 * patients (their own read-only visit history portal). They use
 * different session keys so one doesn't interfere with the other.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------
   STAFF
   --------------------------------------------------------- */
function isLoggedIn(): bool
{
    return isset($_SESSION['staff_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentStaffName(): string
{
    return $_SESSION['staff_name'] ?? 'Staff';
}

function currentStaffRole(): string
{
    return $_SESSION['staff_role'] ?? 'Staff';
}

/* ---------------------------------------------------------
   PATIENTS
   --------------------------------------------------------- */
function isPatientLoggedIn(): bool
{
    return isset($_SESSION['patient_id']);
}

function requirePatientLogin(): void
{
    if (!isPatientLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentPatientName(): string
{
    return $_SESSION['patient_name'] ?? 'Patient';
}

/* ---------------------------------------------------------
   FLASH MESSAGES (shared by both)
   --------------------------------------------------------- */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
