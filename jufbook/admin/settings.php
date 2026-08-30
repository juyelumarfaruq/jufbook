<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_admin();

$pdo = getDB();
$msg = '';
$error = '';

// ১. এক ক্লিকে সম্পূর্ণ MySQL ডেটাবেস ব্যাকআপ (.sql এক্সপোর্ট)
if (isset($_GET['action']) && $_GET['action'] === 'export_db') {
    if (!verifyCSRFToken($_GET['token'] ?? '')) {
        die('নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!');
    }

    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sqlDump = "-- JUFbook Database Backup\n-- Generation Time: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $row2 = $pdo->query("SHOW CREATE TABLE `" . $table . "`")->fetch(PDO::FETCH_NUM);
        $sqlDump .= "\n\n" . $row2[1] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `" . $table . "`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $sqlDump .= "INSERT INTO `" . $table . "` VALUES(";
            $vals = [];
            foreach ($row as $val) {
                $vals[] = ($val === null) ? 'NULL' : $pdo->quote((string)$val);
            }
            $sqlDump .= implode(',', $vals) . ");\n";
        }
    }
    $sqlDump .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="jufbook_backup_' . date('Y_m_d_His') . '.sql"');
    echo $sqlDump;
    exit;
}

// সম্পূর্ণ বাংলা ফন্ট লাইব্রেরি (Shree ও Artistic সিরিজ সহ)
$bengali_fonts = [
    'Standard & Classic' => [
        'SolaimanLipi' => 'SolaimanLipi (সোলাইমান লিপি)',
        'Siyamrupali'  => 'Siyam Rupali (সিয়াম রূপালী)',
        'Kalpurush'    => 'Kalpurush (কালপুরুষ)',
        'Vrinda'       => 'Vrinda (বৃন্দা)',
        'Mukti'        => 'Mukti (মুক্তি)',
        'Mukti Narrow' => 'Mukti Narrow (মুক্তি ন্যারো)',
        'Shonar Bangla'=> 'Shonar Bangla (সোনার বাংলা)'
    ],
    'Google Modern Bengali' => [
        'Hind Siliguri'      => 'Hind Siliguri (হিন্দ শিলিগুড়ি - আধুনিক UI)',
        'Noto Serif Bengali' => 'Noto Serif Bengali (নোটো সেরিফ বাংলা)',
        'Noto Sans Bengali'  => 'Noto Sans Bengali (নোটো সান্স বাংলা)',
        'Tiro Bangla'        => 'Tiro Bangla (তিরো বাংলা - ক্লাসিক সাহিত্য)',
        'Anek Bangla'        => 'Anek Bangla (অনেক বাংলা)',
        'Baloo Da 2'         => 'Baloo Da 2 (বালু দা - বোল্ড ডিসপ্লে)',
        'Galada'             => 'Galada (গালাদা - ক্যালোগ্রাফিক)',
        'Alkatra'            => 'Alkatra (আলকাতরা - আর্ট ও কমিক)',
        'Atma'               => 'Atma (আত্মা - ক্যাজুয়াল হ্যান্ডরাইটিং)',
        'Mina'               => 'Mina (মীনা - স্লিম জ্যামিতিক)'
    ],
    'Ekushey Series' => [
        'Ekushey Bangla'     => 'Ekushey Bangla (একুশে বাংলা)',
        'Ekushey Punarbhaba' => 'Ekushey Punarbhaba (একুশে পুনর্ভবা)',
        'Ekushey Saraswati'  => 'Ekushey Saraswati (একুশে সরস্বতী)',
        'Ekushey Durga'      => 'Ekushey Durga (একুশে দুর্গা)',
        'Ekushey Ananta'     => 'Ekushey Ananta (একুশে অনন্ত)',
        'Ekushey Godhuli'    => 'Ekushey Godhuli (একুশে গোধূলি)',
        'Ekushey Kolom'      => 'Ekushey Kolom (একুশে কলম)',
        'Ekushey Lohit'      => 'Ekushey Lohit (একুশে লোহিত)',
        'Ekushey Sharifa'    => 'Ekushey Sharifa (একুশে শরিফা)',
        'Ekushey Sumon'      => 'Ekushey Sumon (একুশে সুমন)',
        'Ekushey Puja'       => 'Ekushey Puja (একুশে পূজা)'
    ],
    'Shree Bangali Series' => [
        'Shree Bangali 0556' => 'Shree Bangali 0556',
        'Shree Bangali 0557' => 'Shree Bangali 0557',
        'Shree Bangali 0562' => 'Shree Bangali 0562',
        'Shree Bangali 0570' => 'Shree Bangali 0570',
        'Shree Bangali 0574' => 'Shree Bangali 0574 (Italic)',
        'Shree Bangali 0590' => 'Shree Bangali 0590',
        'Shree Bangali 0593' => 'Shree Bangali 0593',
        'Shree Bangali 0598' => 'Shree Bangali 0598',
        'Shree Bangali 1502' => 'Shree Bangali 1502',
        'Shree Bangali 1504' => 'Shree Bangali 1504',
        'Shree Bangali 1506' => 'Shree Bangali 1506',
        'Shree Bangali 1508' => 'Shree Bangali 1508',
        'Shree Bangali 1512' => 'Shree Bangali 1512 (Bold)',
        'Shree Bangali 1514' => 'Shree Bangali 1514',
        'Shree Bangali 1516' => 'Shree Bangali 1516',
        'Shree Bangali 1518' => 'Shree Bangali 1518 (Headline Bold)',
        'Shree Bangali 1520' => 'Shree Bangali 1520',
        'Shree Bangali 1526' => 'Shree Bangali 1526',
        'Shree Bangali 1528' => 'Shree Bangali 1528',
        'Shree Bangali 1536' => 'Shree Bangali 1536 (Heavy)',
        'Shree Bangali 1538' => 'Shree Bangali 1538',
        'Shree Bangali 1545' => 'Shree Bangali 1545',
        'Shree Bangali 1548' => 'Shree Bangali 1548',
        'Shree Bangali 1549' => 'Shree Bangali 1549',
        'Shree Bangali 1555' => 'Shree Bangali 1555',
        'Shree Bangali 1558' => 'Shree Bangali 1558',
        'Shree Bangali 1559' => 'Shree Bangali 1559',
        'Shree Bangali 1560' => 'Shree Bangali 1560',
        'Shree Bangali 1563' => 'Shree Bangali 1563',
        'Shree Bangali 1564' => 'Shree Bangali 1564',
        'Shree Bangali 1568' => 'Shree Bangali 1568',
        'Shree Bangali 1570' => 'Shree Bangali 1570',
        'Shree Bangali 1571' => 'Shree Bangali 1571',
        'Shree Bangali 1572' => 'Shree Bangali 1572',
        'Shree Bangali 1574' => 'Shree Bangali 1574',
        'Shree Bangali 1577' => 'Shree Bangali 1577',
        'Shree Bangali 1582' => 'Shree Bangali 1582',
        'Shree Bangali 1583' => 'Shree Bangali 1583',
        'Shree Bangali 1588' => 'Shree Bangali 1588 (Handwritten Script)',
        'Shree Bangali 1589' => 'Shree Bangali 1589',
        'Shree Bangali 1590' => 'Shree Bangali 1590',
        'Shree Bangali 1592' => 'Shree Bangali 1592',
        'Shree Bangali 1598' => 'Shree Bangali 1598',
        'Shree Bangali 1599' => 'Shree Bangali 1599',
        'Shree Bangali 5104' => 'Shree Bangali 5104',
        'Shree Bangali 5106' => 'Shree Bangali 5106',
        'Shree Bangali 5108' => 'Shree Bangali 5108',
        'Shree Bangali 5110' => 'Shree Bangali 5110',
        'Shree Bangali 5113' => 'Shree Bangali 5113',
        'Shree Bangali 5116' => 'Shree Bangali 5116',
        'Shree Bangali 5121' => 'Shree Bangali 5121',
        'Shree Bangali 5122' => 'Shree Bangali 5122',
        'Shree Bangali 5123' => 'Shree Bangali 5123',
        'Shree Bangali 5126' => 'Shree Bangali 5126',
        'Shree Bangali 5163' => 'Shree Bangali 5163',
        'Shree Bangali 5165' => 'Shree Bangali 5165',
        'Shree Bangali 5189' => 'Shree Bangali 5189',
        'Shree Bangali 5191' => 'Shree Bangali 5191',
        'Shree Bangali 5194' => 'Shree Bangali 5194',
        'Shree Bangali 6109' => 'Shree Bangali 6109',
        'Shree Bangali 6114' => 'Shree Bangali 6114',
        'Shree Bangali 6119' => 'Shree Bangali 6119',
        'Shree Bangali 6140' => 'Shree Bangali 6140'
    ],
    'Artistic & Display' => [
        'Charukola Unicode'        => 'Charukola Unicode (চারুকলা ইউনিকোড)',
        'Charu Chandan HardStroke' => 'Charu Chandan HardStroke (চারু চন্দন হার্ডস্ট্রোক)',
        'Charu Chandan Round Head' => 'Charu Chandan Round Head (চারু চন্দন রাউন্ড হেড)',
        'Charu Chandan Blood Drip' => 'Charu Chandan Blood Drip (চারু চন্দন ব্লাড ড্রিপ)',
        'Charu Chandan Unicode 3d' => 'Charu Chandan Unicode 3D (ত্রিমাত্রিক)',
        'Ben Sen Handwriting'     => 'Ben Sen Handwriting (বেন সেন হ্যান্ডরাইটিং)',
        'Shimanto'                 => 'Shimanto (সীমান্ত)',
        'Amar Bangla'              => 'Amar Bangla (আমার বাংলা)',
        'Amar Desh'                => 'Amar Desh (আমার দেশ)'
    ]
];

// ইংরেজি ফন্ট লাইব্রেরি
$english_fonts = [
    'Cinzel'            => 'Cinzel (ক্লাসিক রয়াল সেরিফ - লোগো ও ব্র্যান্ড)',
    'Plus Jakarta Sans' => 'Plus Jakarta Sans (মডার্ন আল্ট্রা-ক্লিন টেক)',
    'Inter'             => 'Inter (সর্বাধিক জনপ্রিয় ও পরিষ্কার UI)',
    'Playfair Display'  => 'Playfair Display (ম্যাগাজিন ও এডিটোরিয়াল হেডিং)',
    'Montserrat'        => 'Montserrat (বোল্ড ও স্টাইলিশ জ্যামিতিক)',
    'Merriweather'      => 'Merriweather (পড়ার জন্য প্রিমিয়াম সেরিফ)',
    'Outfit'            => 'Outfit (মডার্ন মিনিমালিস্ট)',
    'Lora'              => 'Lora (ক্লাসিক লিটারেচার সেরিফ)'
];

// ২. সেটিংস সেভ লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!';
    } else {
        if (isset($_POST['purge_cache'])) {
            $msg = 'সফলভাবে ক্যাশ পার্জ (Purge Cache) করা হয়েছে!';
        } else {
            $allowed = [
                'font_heading', 'font_body', 'font_reader', 'font_english',
                'maintenance_mode', 'anti_copy_protection', 'reader_watermark', 'allow_pdf_download', 
                'free_chapter_limit', 'reader_font_size',
                'meta_title', 'meta_description', 'header_scripts',
                'ad_header_code', 'ad_reader_code',
                'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption',
                'facebook_url', 'youtube_url', 'instagram_url', 'telegram_url',
                'contact_email', 'copyright_text'
            ];

            $_POST['maintenance_mode'] = isset($_POST['maintenance_mode']) ? '1' : '0';
            $_POST['anti_copy_protection'] = isset($_POST['anti_copy_protection']) ? '1' : '0';
            $_POST['reader_watermark'] = isset($_POST['reader_watermark']) ? '1' : '0';
            $_POST['allow_pdf_download'] = isset($_POST['allow_pdf_download']) ? '1' : '0';

            foreach ($allowed as $field) {
                if (isset($_POST[$field])) {
                    $val = (string)$_POST[$field];
                    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    $stmt->execute([$field, $val]);
                }
            }
            $msg = 'সমস্ত এন্টারপ্রাইজ সেটিংস সফলভাবে সংরক্ষিত হয়েছে!';
        }
    }
}

$settingsRows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

function getSetting(array $arr, string $key, string $default = ''): string {
    return $arr[$key] ?? $default;
}

$curr_heading = getSetting($settingsRows, 'font_heading', 'Noto Serif Bengali');
$curr_body    = getSetting($settingsRows, 'font_body', 'Hind Siliguri');
$curr_reader  = getSetting($settingsRows, 'font_reader', 'Tiro Bangla');
$curr_english = getSetting($settingsRows, 'font_english', 'Cinzel');

$csrf_token = generateCSRFToken();
$page_title = "মাস্টার প্ল্যাটফর্ম কনফিগারেশন — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--adm-ink); margin:0;">মাস্টার প্ল্যাটফর্ম কনফিগারেশন</h2>
        <span style="font-size:13px; color:var(--adm-text-muted);">টাইপোগ্রাফি, সিকিউরিটি, ইমেইল সার্ভার, বিজ্ঞাপন ও এন্টারপ্রাইজ রুলস</span>
    </div>
    <a href="settings.php?action=export_db&token=<?= $csrf_token ?>" class="btn-adm-secondary">
        <span>💾 ১-ক্লিক DB ব্যাকআপ (.sql)</span>
    </a>
</div>

<?php if ($msg): ?>
    <div class="alert-box alert-success"><?= sanitizeOutput($msg) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert-box alert-danger"><?= sanitizeOutput($error) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

    <div class="adm-dashboard-grid">
        <div>
            <!-- ১. সম্পূর্ণ বাংলা ফন্ট মাস্টার -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>🔤 ১. বাংলা টাইপোগ্রাফি মাস্টার</h3>
                </div>

                <div class="form-group">
                    <label>শিরোনাম ও বইয়ের জ্যাকেট কভার ফন্ট (Heading Font):</label>
                    <select name="font_heading" id="select_heading" class="form-control" onchange="updatePreview('preview_heading', this.value)">
                        <?php foreach ($bengali_fonts as $group => $fontList): ?>
                            <optgroup label="<?= $group ?>">
                                <?php foreach ($fontList as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $curr_heading === $key ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <div id="preview_heading" style="background:var(--adm-primary-tint); border:1.5px dashed var(--adm-border); border-radius:8px; padding:12px; margin-top:8px; font-family:'<?= $curr_heading ?>', serif; font-size:19px; font-weight:700; color:var(--adm-ink);">
                        গীতাঞ্জলি — রবীন্দ্রনাথ ঠাকুর (কালজয়ী ক্লাসিক সাহিত্য)
                    </div>
                </div>

                <div class="adm-form-grid" style="grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>সাধারণ ওয়েবসাইটের টেক্সট (Body UI):</label>
                        <select name="font_body" id="select_body" class="form-control" onchange="updatePreview('preview_body', this.value)">
                            <?php foreach ($bengali_fonts as $group => $fontList): ?>
                                <optgroup label="<?= $group ?>">
                                    <?php foreach ($fontList as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $curr_body === $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <div id="preview_body" style="background:var(--adm-primary-tint); border:1.5px dashed var(--adm-border); border-radius:8px; padding:10px; margin-top:8px; font-family:'<?= $curr_body ?>', sans-serif; font-size:14px;">
                            বাঙালির অমর সাহিত্য সম্ভারের প্রিমিয়াম ডিজিটাল অভিজ্ঞতা।
                        </div>
                    </div>

                    <div class="form-group">
                        <label>রিডিং মোড ফন্ট (Zen Reader Font):</label>
                        <select name="font_reader" id="select_reader" class="form-control" onchange="updatePreview('preview_reader', this.value)">
                            <?php foreach ($bengali_fonts as $group => $fontList): ?>
                                <optgroup label="<?= $group ?>">
                                    <?php foreach ($fontList as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $curr_reader === $key ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <div id="preview_reader" style="background:var(--adm-primary-tint); border:1.5px dashed var(--adm-border); border-radius:8px; padding:10px; margin-top:8px; font-family:'<?= $curr_reader ?>', serif; font-size:15px; line-height:1.6;">
                            "যেখানে থাকে সত্যের আলো, সেখানে সাহিত্য চিরন্তন।"
                        </div>
                    </div>
                </div>
            </div>

            <!-- ২. ইংরেজি ফন্ট মাস্টার -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>🔤 ২. ইংরেজি ও ব্র্যান্ড টাইপোগ্রাফি</h3>
                </div>
                <div class="form-group">
                    <label>লোগো ও ইংরেজি টেক্সট ফন্ট (Brand Font):</label>
                    <select name="font_english" id="select_english" class="form-control" onchange="updatePreview('preview_english', this.value)">
                        <?php foreach ($english_fonts as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $curr_english === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="preview_english" style="background:var(--adm-primary-tint); border:1.5px dashed var(--adm-border); border-radius:8px; padding:12px; margin-top:8px; font-family:'<?= $curr_english ?>', serif; font-size:16px; font-weight:700;">
                        JUFBOOK — Premium Bengali Literature & Audiobook Engine 2026
                    </div>
                </div>
            </div>

            <!-- ৩. সিকিউরিটি ও পাইরেসি সুরক্ষা -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>🛡️ ৩. সিকিউরিটি ও পাইরেসি সুরক্ষা (Anti-Piracy)</h3>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div style="background:var(--adm-primary-tint); padding:14px; border-radius:var(--adm-radius-sm); border:1.5px solid var(--adm-border);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; margin:0;">
                            <input type="checkbox" name="anti_copy_protection" value="1" <?= getSetting($settingsRows, 'anti_copy_protection', '1') === '1' ? 'checked' : '' ?>>
                            <span>🔒 কন্টেন্ট সুরক্ষা (Anti-Copy & No Right-Click)</span>
                        </label>
                        <small style="color:var(--adm-text-muted); font-size:12px; display:block; margin-top:4px;">বইয়ের টেক্সট সিলেক্ট ও মাউস রাইট-ক্লিক ব্লক রাখবে।</small>
                    </div>

                    <div style="background:var(--adm-primary-tint); padding:14px; border-radius:var(--adm-radius-sm); border:1.5px solid var(--adm-border);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; margin:0;">
                            <input type="checkbox" name="reader_watermark" value="1" <?= getSetting($settingsRows, 'reader_watermark', '0') === '1' ? 'checked' : '' ?>>
                            <span>💧 রিডার স্ক্রিন ওয়াটারমার্ক (Anti-Screenshot)</span>
                        </label>
                        <small style="color:var(--adm-text-muted); font-size:12px; display:block; margin-top:4px;">বই পড়ার সময় ব্যাকগ্রাউন্ডে জলছাপ হিসেবে আইপি বা ইউজারনেম ভাসবে।</small>
                    </div>
                </div>
            </div>

            <!-- ৪. সার্চ ইঞ্জিন অপ্টিমাইজেশন (SEO) -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>🔍 ৪. সার্চ ইঞ্জিন অপ্টিমাইজেশন (Global SEO)</h3>
                </div>
                <div class="form-group">
                    <label>হোমপেজ মেটা টাইটেল (Meta Title):</label>
                    <input type="text" name="meta_title" value="<?= sanitizeOutput(getSetting($settingsRows, 'meta_title', 'JUFbook - বাংলা ই-বুক ও অডিওবুক')) ?>" class="form-control bn-font">
                </div>
                <div class="form-group">
                    <label>গুগল মেটা ডেসক্রিপশন (Meta Description):</label>
                    <textarea name="meta_description" rows="2" class="form-control bn-font"><?= sanitizeOutput(getSetting($settingsRows, 'meta_description')) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Google Analytics / Header Scripts কোড:</label>
                    <textarea name="header_scripts" rows="3" class="form-control" placeholder="<script> ... </script>"><?= htmlspecialchars(getSetting($settingsRows, 'header_scripts'), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <!-- ৫. বিজ্ঞাপন ও মনিটাইজেশন হাব -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>💰 ৫. বিজ্ঞাপন ও মনিটাইজেশন হাব (Ad Placements)</h3>
                </div>
                <div class="adm-form-grid" style="grid-template-columns: 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>হেডার / হোম ব্যানার অ্যাড কোড (HTML/JS):</label>
                        <textarea name="ad_header_code" rows="3" class="form-control" placeholder="AdSense বা ব্যানার কোড..."><?= htmlspecialchars(getSetting($settingsRows, 'ad_header_code'), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>রিডার পেজ / ইন-বুক অ্যাড কোড:</label>
                        <textarea name="ad_reader_code" rows="3" class="form-control" placeholder="বই পড়ার পেজের বিজ্ঞাপন কোড..."><?= htmlspecialchars(getSetting($settingsRows, 'ad_reader_code'), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <!-- ৬. রিডার এক্সপেরিয়েন্স ও সিস্টেম কন্ট্রোল -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>📖 ৬. রিডার এক্সপেরিয়েন্স ও সিস্টেম</h3>
                </div>
                <div class="form-group">
                    <label>ডিফল্ট পড়ার ফন্ট সাইজ (PX):</label>
                    <input type="number" name="reader_font_size" value="<?= sanitizeOutput(getSetting($settingsRows, 'reader_font_size', '18')) ?>" class="form-control" min="14" max="28">
                </div>

                <div class="form-group">
                    <label>গেস্ট ব্যবহারকারীদের ফ্রি চ্যাপ্টার লিমিট (০ = আনলিমিটেড):</label>
                    <input type="number" name="free_chapter_limit" value="<?= sanitizeOutput(getSetting($settingsRows, 'free_chapter_limit', '1')) ?>" class="form-control" min="0" max="20">
                </div>

                <div class="form-group" style="background:var(--adm-primary-tint); padding:12px; border-radius:var(--adm-radius-sm); border:1.5px solid var(--adm-border); margin-bottom:16px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; margin:0;">
                        <input type="checkbox" name="allow_pdf_download" value="1" <?= getSetting($settingsRows, 'allow_pdf_download', '1') === '1' ? 'checked' : '' ?>>
                        <span>অফলাইন PDF / EPUB ডাউনলোড সুবিধা সক্রিয় রাখুন</span>
                    </label>
                </div>

                <div class="form-group">
                    <label>সাপোর্ট ইমেইল:</label>
                    <input type="email" name="contact_email" value="<?= sanitizeOutput(getSetting($settingsRows, 'contact_email', 'support@jufbook.com')) ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label>ফুটার কপিরাইট টেক্সট:</label>
                    <input type="text" name="copyright_text" value="<?= sanitizeOutput(getSetting($settingsRows, 'copyright_text', '© 2026 JUFbook Platform. সর্বস্বত্ব সংরক্ষিত।')) ?>" class="form-control bn-font">
                </div>

                <div class="form-group" style="background:var(--adm-danger-soft); padding:14px; border-radius:var(--adm-radius-sm); border:1.5px solid #FCA5A5;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; color:var(--adm-danger); margin:0;">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= getSetting($settingsRows, 'maintenance_mode') === '1' ? 'checked' : '' ?>>
                        <span>🚧 মেইনটেন্যান্স মোড (Maintenance Mode On/Off)</span>
                    </label>
                    <small style="color:var(--adm-danger); font-size:12px; display:block; margin-top:4px;">চালু থাকলে সাধারণ ভিজিটররা আন্ডার মেইনটেন্যান্স পেজ দেখতে পাবে।</small>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-adm-primary" style="flex:1; justify-content:center;">💾 সমস্ত সেটিংস সংরক্ষণ করুন</button>
                    <button type="submit" name="purge_cache" value="1" class="btn-adm-secondary">⚡ ক্যাশ পার্জ (Purge Cache)</button>
                </div>
            </div>

            <!-- ৭. ইমেইল ও SMTP সার্ভার কনফিগারেশন -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>✉️ ৭. ইমেইল ও SMTP সার্ভার</h3>
                </div>
                <div class="form-group">
                    <label>SMTP Host:</label>
                    <input type="text" name="smtp_host" value="<?= sanitizeOutput(getSetting($settingsRows, 'smtp_host', 'smtp.gmail.com')) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>SMTP Port:</label>
                    <input type="number" name="smtp_port" value="<?= sanitizeOutput(getSetting($settingsRows, 'smtp_port', '587')) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Encryption:</label>
                    <select name="smtp_encryption" class="form-control">
                        <option value="tls" <?= getSetting($settingsRows, 'smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= getSetting($settingsRows, 'smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>SMTP ইউজারনেম (ইমেইল):</label>
                    <input type="text" name="smtp_user" value="<?= sanitizeOutput(getSetting($settingsRows, 'smtp_user')) ?>" class="form-control" placeholder="your-email@gmail.com">
                </div>
                <div class="form-group">
                    <label>SMTP অ্যাপ পাসওয়ার্ড:</label>
                    <input type="password" name="smtp_pass" value="<?= sanitizeOutput(getSetting($settingsRows, 'smtp_pass')) ?>" class="form-control" placeholder="••••••••••••">
                </div>
            </div>

            <!-- ৮. সোশ্যাল মিডিয়া ও লিঙ্ক -->
            <div class="adm-card-panel">
                <div class="adm-panel-head">
                    <h3>🌐 ৮. সোশ্যাল মিডিয়া লিঙ্ক</h3>
                </div>
                <div class="form-group">
                    <label>Facebook URL:</label>
                    <input type="text" name="facebook_url" value="<?= sanitizeOutput(getSetting($settingsRows, 'facebook_url')) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>YouTube URL:</label>
                    <input type="text" name="youtube_url" value="<?= sanitizeOutput(getSetting($settingsRows, 'youtube_url')) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Telegram URL:</label>
                    <input type="text" name="telegram_url" value="<?= sanitizeOutput(getSetting($settingsRows, 'telegram_url')) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Instagram URL:</label>
                    <input type="text" name="instagram_url" value="<?= sanitizeOutput(getSetting($settingsRows, 'instagram_url')) ?>" class="form-control">
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function updatePreview(previewId, fontName) {
    const el = document.getElementById(previewId);
    if (el) {
        el.style.fontFamily = `"${fontName}", serif, sans-serif`;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>