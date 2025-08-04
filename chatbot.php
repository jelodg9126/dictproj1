<?php
// 1. Database connection (use project standard)
include __DIR__ . '/App/Model/connect.php';

// 2. Get the message from the frontend
$userMessage = $_POST['message'] ?? '';

// Greeting handler
$greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];
if (in_array(strtolower(trim($userMessage)), $greetings)) {
    echo "👋 Hello! How can I assist you today? Please choose an option below.";
    exit;
}

// --- Analytics Questions ---
$analyticsHandled = false;
if (stripos($userMessage, 'total documents sent today') !== false) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM maindoc WHERE DATE(dateAndTime) = CURDATE() AND addressTo IS NOT NULL AND addressTo != '' AND senderName IS NOT NULL AND senderName != ''");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    echo "📤 Total documents sent today: <b>$count</b>";
    $stmt->close();
    $analyticsHandled = true;
    exit;
} elseif (stripos($userMessage, 'total documents received today') !== false) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM maindoc WHERE DATE(dateReceived) = CURDATE()");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    echo "📥 Total documents received today: <b>$count</b>";
    $stmt->close();
    $analyticsHandled = true;
    exit;
} elseif (stripos($userMessage, 'total documents currently in the system') !== false) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM maindoc");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    echo "📚 Total documents currently in the system: <b>$count</b>";
    $stmt->close();
    $analyticsHandled = true;
    exit;
} elseif (stripos($userMessage, 'total pending documents') !== false) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM maindoc WHERE status = 'pending'");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    echo "⏳ Total pending documents: <b>$count</b>";
    $stmt->close();
    $analyticsHandled = true;
    exit;
} elseif (stripos($userMessage, 'total documents endorsed today') !== false) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM maindoc WHERE DATE(endorsementTimestamp) = CURDATE()");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    echo "✅ Total documents endorsed today: <b>$count</b>";
    $stmt->close();
    $analyticsHandled = true;
    exit;
}

// Try to extract a document title in a more flexible way
$docTitle = null;
// 1. Try to match after "document"
if (preg_match('/document[^\w]*(.+)/i', $userMessage, $matches)) {
    $docTitle = trim($matches[1]);
}
// 2. Try to match after "where is", "track", "status of", etc.
elseif (preg_match('/(?:where is|track|status of|find|locate)[^\w]*(.+)/i', $userMessage, $matches)) {
    $docTitle = trim($matches[1]);
}
// 3. Try to match quoted text
elseif (preg_match('/[\"“”\']([^\"“”\']+)[\"“”\']/u', $userMessage, $matches)) {
    $docTitle = trim($matches[1]);
}

// Remove trailing punctuation
if ($docTitle) {
    $docTitle = preg_replace('/[?.!]+$/', '', $docTitle);
    $stmt = $conn->prepare("SELECT doctitle, status, addressTo, receivedBy, dateReceived FROM maindoc WHERE doctitle LIKE ? LIMIT 1");
    $likeTitle = "%$docTitle%";
    $stmt->bind_param("s", $likeTitle);
    $stmt->execute();
    $stmt->bind_result($foundTitle, $status, $addressTo, $receivedBy, $dateReceived);
    if ($stmt->fetch()) {
        if (strtolower($status) === 'pending') {
            // Office display name mapping (from Outgoing.php)
            $officeDisplayNames = [
                'dictbulacan' => 'Provincial Office Bulacan',
                'dictaurora' => 'Provincial Office Aurora',
                'dictbataan' => 'Provincial Office Bataan',
                'dictpampanga' => 'Provincial Office Pampanga',
                'dictPampanga' => 'Provincial Office Pampanga',
                'dicttarlac' => 'Provincial Office Tarlac',
                'dictzambales' => 'Provincial Office Zambales',
                'dictothers' => 'Provincial Office Others',
                'dictNE' => 'Provincial Office Nueva Ecija',
                'dictne' => 'Provincial Office Nueva Ecija',
                'dictNUEVAECIJA' => 'Provincial Office Nueva Ecija',
                'maindoc' => 'DICT Region 3 Office',
                'Rdictpampanga' => 'Provincial Office Pampanga',
                'RdictPampanga' => 'Provincial Office Pampanga',
                'RdictTarlac' => 'Provincial Office Tarlac',
                'RdictBataan' => 'Provincial Office Bataan',
                'RdictBulacan' => 'Provincial Office Bulacan',
                'RdictAurora' => 'Provincial Office Aurora',
                'RdictZambales' => 'Provincial Office Zambales',
                'RdictNuevaEcija' => 'Provincial Office Nueva Ecija',
                'RdictNE' => 'Provincial Office Nueva Ecija',
                'Rmaindoc' => 'DICT Region 3 Office',
                // Add more as you encounter new codes!
            ];
            function getOfficeDisplayName($code, $map) {
                if (!$code) return '';
                $lower = strtolower($code);
                foreach ($map as $key => $val) {
                    if (strtolower($key) === $lower) return $val;
                }
                return $code;
            }
            $officeDisplay = getOfficeDisplayName($addressTo, $officeDisplayNames);
            echo "📦 The document '$foundTitle' is currently on the way to its destination office: <b>$officeDisplay</b>.";
        } elseif (strtolower($status) === 'received') {
            // Office display name mapping (from Outgoing.php)
            $officeDisplayNames = [
                'dictbulacan' => 'Provincial Office Bulacan',
                'dictaurora' => 'Provincial Office Aurora',
                'dictbataan' => 'Provincial Office Bataan',
                'dictpampanga' => 'Provincial Office Pampanga',
                'dictPampanga' => 'Provincial Office Pampanga',
                'dicttarlac' => 'Provincial Office Tarlac',
                'dictzambales' => 'Provincial Office Zambales',
                'dictothers' => 'Provincial Office Others',
                'dictNE' => 'Provincial Office Nueva Ecija',
                'dictne' => 'Provincial Office Nueva Ecija',
                'dictNUEVAECIJA' => 'Provincial Office Nueva Ecija',
                'maindoc' => 'DICT Region 3 Office',
                'Rdictpampanga' => 'Provincial Office Pampanga',
                'RdictPampanga' => 'Provincial Office Pampanga',
                'RdictTarlac' => 'Provincial Office Tarlac',
                'RdictBataan' => 'Provincial Office Bataan',
                'RdictBulacan' => 'Provincial Office Bulacan',
                'RdictAurora' => 'Provincial Office Aurora',
                'RdictZambales' => 'Provincial Office Zambales',
                'RdictNuevaEcija' => 'Provincial Office Nueva Ecija',
                'RdictNE' => 'Provincial Office Nueva Ecija',
                'Rmaindoc' => 'DICT Region 3 Office',
                // Add more as you encounter new codes!
            ];
            function getOfficeDisplayName($code, $map) {
                if (!$code) return '';
                $lower = strtolower($code);
                foreach ($map as $key => $val) {
                    if (strtolower($key) === $lower) return $val;
                }
                return $code;
            }
            $officeDisplay = getOfficeDisplayName($addressTo, $officeDisplayNames);
            $dateStr = $dateReceived ? date('F j, Y', strtotime($dateReceived)) : 'an unknown date';
            echo "✅ The document '$foundTitle' was received by <b>$receivedBy</b> of <b>$officeDisplay</b> on <b>$dateStr</b>.";
        } else {
            echo "📄 The document '$foundTitle' currently has a status of '<b>$status</b>'.";
        }
        exit;
    } else {
        echo "⚠️ I couldn't find a document matching '<b>$docTitle</b>' in the system.";
        exit;
    }
    $stmt->close();
} 