<?php
session_start();

// 데이터베이스 연결 설정
function getDBConnection() {
    $dsn = 'sqlite:./course_registration.db';
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("DB 연결 실패: " . $e->getMessage());
    }
}

// 데이터베이스 초기화
function initializeDatabase() {
    $pdo = getDBConnection();

    // 학생 테이블
    $sqlStudents = "
        CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            student_id TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )
    ";
    $pdo->exec($sqlStudents);

    // 강좌 테이블
    $sqlCourses = "
        CREATE TABLE IF NOT EXISTS courses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            course_code TEXT NOT NULL UNIQUE
        )
    ";
    $pdo->exec($sqlCourses);

    // 수강신청 테이블
    $sqlRegistrations = "
        CREATE TABLE IF NOT EXISTS registrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id TEXT NOT NULL,
            course_id INTEGER NOT NULL,
            registration_time DATETIME DEFAULT (DATETIME('now', 'localtime')),
            UNIQUE(student_id, course_id),
            FOREIGN KEY(course_id) REFERENCES courses(id)
        )
    ";
    $pdo->exec($sqlRegistrations);

    // 기본 강좌 추가
    $defaultCourses = [
        ['캡스톤', 'CPT001'],
        ['기계학습', 'CPT002'],
        ['컴퓨터구조', 'CPT003'],
        ['웹응용', 'CPT004'],
        ['컴퓨터네트워크', 'CPT005'],
        ['빅데이터', 'CPT006'],
        ['데이터베이스', 'CPT007']
    ];
    foreach ($defaultCourses as $course) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO courses (name, course_code) VALUES (:name, :course_code)");
        $stmt->execute(['name' => $course[0], 'course_code' => $course[1]]);
    }
}

// 초기화 기능
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    $pdo = getDBConnection();
    $pdo->exec("DROP TABLE IF EXISTS registrations");
    $pdo->exec("DROP TABLE IF EXISTS courses");
    $pdo->exec("DROP TABLE IF EXISTS students");
    initializeDatabase();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 로그인 처리 (검증 제거)
function login($student_id, $password) {
    // 무조건 로그인 성공 처리
    return [
        'id' => $student_id,
        'name' => '임시 사용자'
    ];
}

// 초기화
initializeDatabase();

// 로그인 여부 확인
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // 로그인 화면
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $student_id = $_POST['student_id'];
        $password = $_POST['password'];

        // 무조건 로그인 성공
        $user = login($student_id, $password);
        $_SESSION['logged_in'] = true;
        $_SESSION['student_id'] = $user['id'];
        $_SESSION['student_name'] = $user['name'];

        // 페이지 새로고침
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // 로그인 화면 출력
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>수강신청 로그인</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f0f4f8;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .login-container {
                    background: white;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    width: 300px;
                    text-align: center;
                }
                input {
                    width: 100%;
                    padding: 10px;
                    margin: 10px 0;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                button {
                    width: 100%;
                    padding: 10px;
                    background-color: #007BFF;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h2>수강신청 로그인</h2>
                <form method="post" action="">
                    <input type="text" name="student_id" placeholder="학번" required>
                    <input type="password" name="password" placeholder="비밀번호" required>
                    <button type="submit" name="login">로그인</button>
                </form>
            </div>
        </body>
        </html>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>수강신청 시스템</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f0f4f8;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .reset-link {
            display: inline-block;
            margin-top: 10px;
            color: #007BFF;
            text-decoration: none;
        }
        .reset-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>수강신청 시스템</h1>
    </header>
    <div class="container">
        <h2>안녕하세요, <?php echo htmlspecialchars($_SESSION['student_name']); ?>님!</h2>
        <a class="reset-link" href="?reset=true">초기화</a>
        <h3>수강신청</h3>
        <form method="post" action="">
            <select name="course_id" required>
                <option value="">강좌 선택</option>
                <?php
                $courses = getDBConnection()->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($courses as $course) {
                    echo "<option value='{$course['id']}'>{$course['name']} ({$course['course_code']})</option>";
                }
                ?>
            </select>
            <button type="submit" name="register">신청</button>
        </form>

        <?php
        // 수강신청 처리
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
            $student_id = $_SESSION['student_id'];
            $course_id = $_POST['course_id'];
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("INSERT INTO registrations (student_id, course_id) VALUES (:student_id, :course_id)");
                $stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
                echo "<p>수강신청이 완료되었습니다.</p>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo "<p style='color:red;'>이미 신청된 강좌입니다.</p>";
                } else {
                    echo "<p style='color:red;'>오류가 발생했습니다: " . $e->getMessage() . "</p>";
                }
            }
        }
        ?>

        <h3>신청한 강좌 목록</h3>
        <ul>
            <?php
            $student_id = $_SESSION['student_id'];
            $stmt = getDBConnection()->prepare("SELECT courses.name AS course_name, courses.course_code, registrations.registration_time FROM registrations INNER JOIN courses ON registrations.course_id = courses.id WHERE registrations.student_id = :student_id ORDER BY registrations.registration_time DESC");
            $stmt->execute(['student_id' => $student_id]);
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($registrations as $registration) {
                echo "<li>{$registration['course_name']} ({$registration['course_code']}) - {$registration['registration_time']}</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>
