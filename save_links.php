<?php
/**
 * =========================================================================
 * سكربت تحديث روابط البث المباشر فورياً
 * CRESPIAF - PCI AFRIQUE
 * =========================================================================
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$jsonFile = __DIR__ . '/streams.json';

// عند طلب GET: إرجاع الروابط الحالية
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($jsonFile)) {
        echo file_get_contents($jsonFile);
    } else {
        echo json_encode([
            'arabic' => 'https://www.youtube.com',
            'french' => 'https://www.youtube.com',
            'english' => 'https://www.youtube.com',
            'last_updated' => date('Y-m-d H:i:s')
        ]);
    }
    exit;
}

// عند طلب POST: تحديث الروابط
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        // محاولة القراءة من $_POST العادية
        $data = $_POST;
    }

    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'لم يتم إرسال أي بيانات']);
        exit;
    }

    // تنظيف الروابط
    $arabic = trim($data['arabic'] ?? '');
    $french = trim($data['french'] ?? '');
    $english = trim($data['english'] ?? '');

    // التحقق من صحة الروابط الأساسية
    $newConfig = [
        'arabic' => $arabic ?: 'https://www.youtube.com',
        'french' => $french ?: 'https://www.youtube.com',
        'english' => $english ?: 'https://www.youtube.com',
        'last_updated' => date('Y-m-d H:i:s')
    ];

    $jsonString = json_encode($newConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $writeOk = @file_put_contents($jsonFile, $jsonString);

    if ($writeOk === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'تعذر حفظ الملف على الخادم، يرجى التحقق من صلاحيات الكتابة لمجلد المشروع.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ الروابط وتطبيقها فوراً على جميع المستخدمين',
        'updated_at' => $newConfig['last_updated'],
        'data' => $newConfig
    ]);
    exit;
}
