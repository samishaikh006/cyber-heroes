CREATE DATABASE IF NOT EXISTS cyber_comic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cyber_comic;

CREATE TABLE IF NOT EXISTS stories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_number INT UNSIGNED NOT NULL UNIQUE,
    title_en VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) NOT NULL,
    description_en TEXT NULL,
    description_hi TEXT NULL,
    lesson_en TEXT NULL,
    lesson_hi TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS story_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    page_number INT UNSIGNED NOT NULL,
    image_en VARCHAR(500) NULL,
    image_hi VARCHAR(500) NULL,
    UNIQUE KEY uq_story_page (story_id, page_number),
    CONSTRAINT fk_story_pages_story FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    question_en TEXT NOT NULL,
    question_hi TEXT NOT NULL,
    option_a_en VARCHAR(500) NOT NULL,
    option_a_hi VARCHAR(500) NOT NULL,
    option_b_en VARCHAR(500) NOT NULL,
    option_b_hi VARCHAR(500) NOT NULL,
    option_c_en VARCHAR(500) NOT NULL,
    option_c_hi VARCHAR(500) NOT NULL,
    option_d_en VARCHAR(500) NOT NULL,
    option_d_hi VARCHAR(500) NOT NULL,
    correct_option ENUM('A','B','C','D') NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT fk_quiz_questions_story FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NULL,
    title_en VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255) NOT NULL,
    description_en TEXT NULL,
    description_hi TEXT NULL,
    youtube_id VARCHAR(50) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_videos_story FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO stories (story_number, title_en, title_hi, description_en, description_hi) VALUES
(1,'OTP Scam','OTP Scam','Learn why you should never share an OTP with an unknown person.','जानें कि OTP किसी अनजान व्यक्ति के साथ साझा क्यों नहीं करना चाहिए।'),
(2,'Phishing Links','Phishing Links','Learn how to identify suspicious links before clicking.','क्लिक करने से पहले संदिग्ध लिंक पहचानना सीखें।'),
(3,'Weak Passwords','Weak Passwords','Learn how weak passwords can put accounts at risk.','जानें कि कमजोर पासवर्ड आपके खातों को कैसे जोखिम में डाल सकते हैं।'),
(4,'Fake Profiles','Fake Profiles','Learn how to spot suspicious or fake online profiles.','संदिग्ध या नकली ऑनलाइन प्रोफाइल पहचानना सीखें।'),
(5,'Fake News & Misinformation','Fake News & Misinformation','Learn to verify information before sharing it.','जानकारी साझा करने से पहले उसे सत्यापित करना सीखें।'),
(6,'Cyberbullying','Cyberbullying','Learn safe and responsible ways to respond to cyberbullying.','साइबरबुलिंग का सुरक्षित और जिम्मेदार तरीके से जवाब देना सीखें।'),
(7,'Online Gaming Scams','Online Gaming Scams','Learn how to avoid scams in online games.','ऑनलाइन गेम्स में होने वाले स्कैम से बचना सीखें।'),
(8,'Privacy & Personal Information','Privacy & Personal Information','Learn how to protect personal information online.','अपनी व्यक्तिगत जानकारी को ऑनलाइन सुरक्षित रखना सीखें।'),
(9,'Malware / Unknown Downloads','Malware / Unknown Downloads','Learn why unknown downloads can be dangerous.','जानें कि अनजान डाउनलोड खतरनाक क्यों हो सकते हैं।'),
(10,'Public Wi-Fi Safety','Public Wi-Fi Safety','Learn safe habits when using public Wi-Fi.','पब्लिक Wi-Fi इस्तेमाल करते समय सुरक्षित आदतें सीखें।'),
(11,'QR Code Scam (Quishing)','QR Code Scam (Quishing)','Learn how QR-code scams can trick users.','जानें कि QR-code scams यूज़र्स को कैसे धोखा दे सकते हैं।'),
(12,'Online Shopping Scam','Online Shopping Scam','Learn how to shop online more safely.','ऑनलाइन खरीदारी को सुरक्षित तरीके से करना सीखें।'),
(13,'Fake Tech Support Scam','Fake Tech Support Scam','Learn how fake support messages try to steal information.','जानें कि नकली टेक सपोर्ट संदेश जानकारी चुराने की कोशिश कैसे करते हैं।'),
(14,'Deepfake Awareness','Deepfake Awareness','Learn how to think critically about manipulated media.','बदले हुए मीडिया के बारे में आलोचनात्मक सोच रखना सीखें।'),
(15,'Digital Footprint','Digital Footprint','Learn how online activity can leave a lasting digital footprint.','जानें कि ऑनलाइन गतिविधियां डिजिटल फुटप्रिंट कैसे छोड़ती हैं।'),
(16,'App Permissions','App Permissions','Learn how to review permissions before installing apps.','ऐप इंस्टॉल करने से पहले permissions जांचना सीखें।'),
(17,'Fake Giveaways & Prize Scams','Fake Giveaways & Prize Scams','Learn how fake prizes are used to trick people.','जानें कि नकली इनाम लोगों को कैसे धोखा देते हैं।'),
(18,'Account Hacking & Recovery','Account Hacking & Recovery','Learn basic steps for protecting and recovering accounts.','खातों को सुरक्षित और रिकवर करने के बुनियादी कदम सीखें।'),
(19,'Safe Use of AI & Chatbots','Safe Use of AI & Chatbots','Learn how to use AI tools without oversharing sensitive data.','संवेदनशील जानकारी साझा किए बिना AI टूल्स का सुरक्षित उपयोग सीखें।'),
(20,'Phishing Emails','Phishing Emails','Learn how to recognize suspicious emails.','संदिग्ध ईमेल पहचानना सीखें।'),
(21,'SIM Swap Scam','SIM Swap Scam','Learn the basics of SIM-swap fraud and account protection.','SIM-swap fraud और अकाउंट सुरक्षा की मूल बातें सीखें।'),
(22,'USB & External Device Safety','USB & External Device Safety','Learn safe practices for unknown USB and external devices.','अनजान USB और बाहरी डिवाइस के लिए सुरक्षित आदतें सीखें।'),
(23,'Screen Sharing Scams','Screen Sharing Scams','Learn why you should be careful with remote screen-sharing requests.','रिमोट screen-sharing requests के साथ सावधानी क्यों जरूरी है, जानें।'),
(24,'Ransomware Awareness','Ransomware Awareness','Learn how ransomware can affect files and devices.','जानें कि ransomware files और devices को कैसे प्रभावित कर सकता है।'),
(25,'Data Backup & Recovery','Data Backup & Recovery','Learn why regular backups matter.','जानें कि नियमित backups क्यों महत्वपूर्ण हैं।'),
(26,'Social Media Privacy Settings','Social Media Privacy Settings','Learn how privacy settings can reduce unnecessary exposure.','जानें कि privacy settings अनावश्यक exposure को कैसे कम कर सकती हैं।'),
(27,'Digital Addiction & Screen Time','Digital Addiction & Screen Time','Learn healthier habits for digital device use.','डिजिटल डिवाइस के स्वस्थ उपयोग की आदतें सीखें।'),
(28,'Two-Factor Authentication (2FA)','Two-Factor Authentication (2FA)','Learn how 2FA adds another layer of account protection.','जानें कि 2FA अकाउंट सुरक्षा की एक अतिरिक्त परत कैसे जोड़ता है।'),
(29,'Software Updates & Security','Software Updates & Security','Learn why security updates should not be ignored.','जानें कि security updates को नजरअंदाज क्यों नहीं करना चाहिए।'),
(30,'Cybersecurity Hero – Final Challenge','Cybersecurity Hero – Final Challenge','Complete the final challenge and review your cybersecurity habits.','अंतिम चुनौती पूरी करें और अपनी साइबर सुरक्षा आदतों की समीक्षा करें.')
ON DUPLICATE KEY UPDATE title_en=VALUES(title_en), title_hi=VALUES(title_hi);
