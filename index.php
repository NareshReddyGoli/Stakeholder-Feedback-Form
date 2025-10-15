<?php
include 'db_connect.php'; // Includes session_start()

$errors = [];
$form_data = [
    'name' => '', 'phone' => '', 'email' => '', 'academic_year' => '', 
    'stakeholder_type' => '', 'gender' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and Collect Data
    $form_data['name'] = trim($_POST['name']);
    $form_data['phone'] = trim($_POST['phone']);
    $form_data['email'] = trim($_POST['email']);
    $form_data['academic_year'] = $_POST['academic_year'];
    $form_data['stakeholder_type'] = $_POST['stakeholder_type'];
    $form_data['gender'] = $_POST['gender'] ?? '';

    // 2. Validation (Step 21)
    if (empty($form_data['name'])) {$errors['name'] = "Name is required.";}
    if (!preg_match("/^[0-9]{10,15}$/", $form_data['phone'])) {$errors['phone'] = "Please enter a valid phone number.";}
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {$errors['email'] = "Invalid email format.";}
    if (empty($form_data['academic_year'])) {$errors['academic_year'] = "Academic Year is required.";}
    if (empty($form_data['stakeholder_type'])) {$errors['stakeholder_type'] = "Stakeholder Type is required.";}
    if (empty($form_data['gender'])) {$errors['gender'] = "Gender is required.";}

    // 3. Process if Valid
    if (empty($errors)) {
        // Prepare and bind (security improvement)
        $stmt = $conn->prepare("INSERT INTO stakeholder_details (name, phone, email, gender, academic_year, stakeholder_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $phone, $email, $gender, $academic_year, $stakeholder_type);
        
        $name = $form_data['name'];
        $phone = $form_data['phone'];
        $email = $form_data['email'];
        $gender = $form_data['gender'];
        $academic_year = $form_data['academic_year'];
        $stakeholder_type = $form_data['stakeholder_type'];
        
        if ($stmt->execute()) {
            $stakeholder_id = $stmt->insert_id;
            
            // Conditional Redirect
            if ($form_data['stakeholder_type'] === 'Student') {
                $_SESSION['stakeholder_id'] = $stakeholder_id;
                // Redirect to Program Details Form (Step 22)
                header("Location: program_details.php");
                exit();
            } else {
                // For Faculty/Alumni, a simpler message/confirmation
                echo "<script>alert('Thank you for your feedback! For Students, the next form appears.'); window.location.href='index.php';</script>";
                exit();
            }
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
    <title>Stakeholder Feedback Form</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div style="text-align: center;">
            <h2>VIGNAN'S</h2>
            <h3>FOUNDATION FOR SCIENCE, TECHNOLOGY & RESEARCH</h3>
            <p>(Deemed to be University) - Estd. u/s 3 of UGC Act 1956</p>
        </div>

        <h2>Stakeholder Feedback Form</h2>
        <h3>PERSONAL DETAILS</h3>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <div class="form-group">
                <label for="name">Name of the Stakeholder*</label>
                <input type="text" id="name" name="name" placeholder="Please enter your firstname"
                    value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                <div class="error"><?php echo $errors['name'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" placeholder="Please enter your phone"
                    value="<?php echo htmlspecialchars($form_data['phone']); ?>" required>
                <div class="error"><?php echo $errors['phone'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Please enter your email"
                    value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                <div class="error"><?php echo $errors['email'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label for="academic_year">Feedback for Academic Year</label>
                <select id="academic_year" name="academic_year" required>
                    <option value="">Select the Academic Year</option>
                    <option value="2022-2023"
                        <?php echo $form_data['academic_year'] === '2022-2023' ? 'selected' : ''; ?>>2022-2023</option>
                </select>
                <div class="error"><?php echo $errors['academic_year'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label for="stakeholder_type">Type of the Stakeholder</label>
                <select id="stakeholder_type" name="stakeholder_type" required>
                    <option value="">Select the type</option>
                    <option value="Student"
                        <?php echo $form_data['stakeholder_type'] === 'Student' ? 'selected' : ''; ?>>Student</option>
                    <option value="Faculty"
                        <?php echo $form_data['stakeholder_type'] === 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
                    <option value="Alumni" <?php echo $form_data['stakeholder_type'] === 'Alumni' ? 'selected' : ''; ?>>
                        Alumni</option>
                </select>
                <div class="error"><?php echo $errors['stakeholder_type'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <input type="radio" id="male" name="gender" value="Male"
                    <?php echo $form_data['gender'] === 'Male' ? 'checked' : ''; ?>>
                <label for="male" style="display:inline; margin-right: 20px;">Male</label>
                <input type="radio" id="female" name="gender" value="Female"
                    <?php echo $form_data['gender'] === 'Female' ? 'checked' : ''; ?>>
                <label for="female" style="display:inline;">Female</label>
                <div class="error"><?php echo $errors['gender'] ?? ''; ?></div>
            </div>

            <button type="submit" class="submit-btn">SUBMIT</button>
            <div class="error"><?php echo $errors['db'] ?? ''; ?></div>
        </form>
    </div>

</body>

</html>