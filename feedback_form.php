<?php
include 'db_connect.php'; 

if (!isset($_SESSION['reg_no'])) {
    header("Location: index.php");
    exit();
}

$reg_no = $_SESSION['reg_no'];
$errors = [];
$form_data = array_fill(1, 7, 3); // Default score to 3
$form_data['suggestions'] = '';

$questions = [
    1 => "Course Contents of Curriculum are in tune with the Program Outcomes",
    2 => "Course Contents are designed to enable Problem Solving Skills and Core competencies",
    3 => "Courses placed in the curriculum serves the needs of both advanced and slow learners",
    4 => "Contact Hour Distribution among the various Course Components (LTP) is Satisfiable",
    5 => "Composition of Basic Sciences, Engineering, Humanities and Management Courses is a right mix and satisfiable",
    6 => "Laboratory sessions are sufficient to improve the technical skills of students",
    7 => "Inclusion of Minor Project/ Mini Projects improved the technical competency and leadership skills among the students",
];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Collect Data
    $valid = true;
    for ($i = 1; $i <= 7; $i++) {
        $key = 'q' . $i;
        $score = $_POST[$key] ?? null;
        if (!is_numeric($score) || $score < 1 || $score > 5) {
            $errors[$key] = "Score for Question $i is required.";
            $valid = false;
        }
        $form_data[$i] = $score;
    }
    $form_data['suggestions'] = trim($_POST['suggestions'] ?? '');
    
    // 2. Process if Valid (Step 69)
    if ($valid) {
        $stmt = $conn->prepare("INSERT INTO student_feedback (reg_no, q1_outcomes, q2_skills, q3_learners, q4_contact_hour, q5_mix, q6_lab, q7_project, suggestions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiiiiis", $reg_no, $q1, $q2, $q3, $q4, $q5, $q6, $q7, $suggestions);
        
        $q1 = $form_data[1]; $q2 = $form_data[2]; $q3 = $form_data[3];
        $q4 = $form_data[4]; $q5 = $form_data[5]; $q6 = $form_data[6];
        $q7 = $form_data[7]; $suggestions = $form_data['suggestions'];
        
        if ($stmt->execute()) {
            unset($_SESSION['stakeholder_id']); // Clear temporary session data
            unset($_SESSION['reg_no']);
            echo "<script>alert('Feedback submitted successfully!'); window.location.href='report_search.php';</script>";
            exit();
        } else {
            $errors['db'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Feedback Questionnaire</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h2>QUESTIONNAIRE FOR STUDENT (IT)</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <?php foreach ($questions as $i => $q_text): ?>
            <div class="rating-row">
                <label class="rating-label"><?php echo $q_text; ?></label>
                <input type="range" min="1" max="5" name="q<?php echo $i; ?>"
                    value="<?php echo htmlspecialchars($form_data[$i]); ?>"
                    oninput="this.nextElementSibling.style.backgroundSize = (this.value-1)*25 + '% 10px';" required>
                <div class="range-labels">
                    <span class="low">1 means Low</span>
                    <span>2</span>
                    <span>3</span>
                    <span>4</span>
                    <span class="high">5 means High</span>
                </div>
                <div class="error"><?php echo $errors['q' . $i] ?? ''; ?></div>
            </div>
            <?php endforeach; ?>

            <div class="form-group">
                <label for="suggestions">Suggest any other points to improve the quality of the Curriculum</label>
                <textarea id="suggestions" name="suggestions"
                    rows="4"><?php echo htmlspecialchars($form_data['suggestions']); ?></textarea>
            </div>

            <button type="submit" class="submit-btn">SUBMIT</button>
            <div class="error"><?php echo $errors['db'] ?? ''; ?></div>
        </form>
    </div>

</body>

</html>