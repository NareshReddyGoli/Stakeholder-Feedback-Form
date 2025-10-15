<?php
include 'db_connect.php'; 

$report_data = null;
$error_message = '';
$reg_no_search = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_no_search = strtoupper(trim($_POST['reg_no']));
    
    if (empty($reg_no_search)) {
        $error_message = "Please enter a Register No.";
    } else {
        // Query to join all three tables and fetch data
        $stmt = $conn->prepare("
            SELECT 
                sd.name, sd.academic_year, 
                sp.reg_no, sp.program, sp.branch, sp.regulation, 
                sf.*
            FROM student_feedback sf
            JOIN student_program sp ON sf.reg_no = sp.reg_no
            JOIN stakeholder_details sd ON sp.stakeholder_id = sd.id
            WHERE sf.reg_no = ?
        ");
        $stmt->bind_param("s", $reg_no_search);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $report_data = $result->fetch_assoc();
        } else {
            $error_message = "No feedback found for Register No: " . htmlspecialchars($reg_no_search);
        }
        $stmt->close();
    }
}

// Define the questions for display
$report_questions = [
    'q1_outcomes' => "Course Contents of Curriculum are in tune with the Program Outcomes",
    'q2_skills' => "Course Contents are designed to enable Problem Solving Skills and Core competencies",
    'q3_learners' => "Courses placed in the curriculum serves the needs of both advanced and slow learners",
    'q4_contact_hour' => "Contact Hour Distribution among the various Course Components (LTP) is Satisfiable",
    'q5_mix' => "Composition of Basic Sciences, Engineering, Humanities and Management Courses is a right mix and satisfiable",
    'q6_lab' => "Laboratory sessions are sufficient to improve the technical skills of students",
    'q7_project' => "Inclusion of Minor Project/ Mini Projects improved the technical competency and leadership skills among the students",
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Feedback Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .report-table td:first-child {
        font-weight: bold;
    }

    .report-header-table td {
        padding: 5px 15px;
        border: 1px solid #ccc;
    }

    .feedback-scores-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .feedback-scores-table th,
    .feedback-scores-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .feedback-scores-table td:last-child {
        text-align: center;
        font-weight: bold;
    }
    </style>
</head>

<body>

    <div class="container">
        <h2>Feedback Report Search</h2>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="reg_no">Register No</label>
                <input type="text" id="reg_no" name="reg_no" value="<?php echo htmlspecialchars($reg_no_search); ?>"
                    required>
                <div class="error"><?php echo $error_message; ?></div>
            </div>
            <button type="submit" class="green-submit-btn">SUBMIT</button>
        </form>

        <?php if ($report_data): ?>
        <hr style="margin-top: 30px;">

        <div style="text-align: center; margin-bottom: 20px;">
            <h3>VIGNAN'S (UNIVERSITY)</h3>
            <h4>Student Feedback Report for <?php echo htmlspecialchars($report_data['academic_year']); ?> (1-5 means
                Low-High)</h4>
        </div>

        <table class="report-header-table" style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td>Reg No.</td>
                <td><?php echo htmlspecialchars($report_data['reg_no']); ?></td>
                <td>Name</td>
                <td><?php echo htmlspecialchars($report_data['name']); ?></td>
            </tr>
            <tr>
                <td>Course</td>
                <td><?php echo htmlspecialchars($report_data['program']); ?></td>
                <td>Branch</td>
                <td><?php echo htmlspecialchars($report_data['branch']); ?></td>
            </tr>
            <tr>
                <td>Academic year</td>
                <td><?php echo htmlspecialchars($report_data['academic_year']); ?></td>
                <td>Regulation</td>
                <td><?php echo htmlspecialchars($report_data['regulation']); ?></td>
            </tr>
        </table>

        <table class="feedback-scores-table">
            <thead>
                <tr>
                    <th style="width: 85%;">Feedback Question</th>
                    <th style="width: 15%;">Score (1-5)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_questions as $db_key => $q_text): ?>
                <tr>
                    <td><?php echo htmlspecialchars($q_text); ?></td>
                    <td><?php echo htmlspecialchars($report_data[$db_key]); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td>**Suggestions**</td>
                    <td><?php echo htmlspecialchars($report_data['suggestions'] ?? 'N/A'); ?></td>
                </tr>
            </tbody>
        </table>

        <?php endif; ?>
    </div>

</body>

</html>