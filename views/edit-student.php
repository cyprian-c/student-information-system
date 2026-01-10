<?php
// This file redirects to admission-form.php with the student ID
header('Location: admission-form.php?id=' . ($_GET['id'] ?? ''));
exit;
