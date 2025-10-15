<?php
include 'db_connect.php'; 

// Check if a stakeholder_id is set in the session (meaning a student submitted index.php)
if (!isset($_SESSION['stakeholder_id'])) {
    header("Location: index.php");
    exit();
}

$errors = [];
$form_data = ['reg_no' => '', 'ugpg' => '', 'program' => '', 'branch' => '', 'regulation' => ''];

// Program and Branch data for dynamic selection
// (Step 32: If UG selected -> Btech, BBA, BCA, BPharm)
// (Step 33: If PG selected -> Mtech, MBA, MCA)
$program_data = [
    'UG' => ['BTech', 'BBA', 'BCA', 'BPharm'],
    'PG' => ['MTech', 'MBA', 'MCA']
];

// (Step 34: Based on the second drop-down value we are selecting, the branch details should be available)
$branch_data = [
    'BTech' => ['CSE', 'IT', 'ECE', 'ME', 'CE'],
    'BBA' => ['Finance', 'HR', 'Marketing'],
    'BCA' => ['CS', 'AIML'],
    'BPharm' => ['Pharmaceutics', 'Pharmacology'],
    'MTech' => ['CSE', 'ECE', 'ME'],
    'MBA' => ['Finance', 'HR', 'Marketing'],
    'MCA' => ['CS', 'Data Science']
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Collect Data
    $form_data['reg_no'] = trim($_POST['reg_no']);
    $form_data['ugpg'] = $_POST['ugpg'];
    $form_data['program'] = $_POST['program'];
    $form_data['branch'] = $_POST['branch'];
    $form_data['regulation'] = $_POST['regulation'];

    // 2. Validation
    // (Step 36: Validate all the fields)
    if (empty($form_data['reg_no'])) {$errors['reg_no'] = "Register No is required.";}
    if (empty($form_data['ugpg'])) {$errors['ugpg'] = "UG/PG selection is required.";}
    if (empty($form_data['program'])) {$errors['program'] = "Program selection is required.";}
    if (empty($form_data['branch'])) {$errors['branch'] = "Branch selection is required.";}
    if (empty($form_data['regulation'])) {$errors['regulation'] = "Regulation selection is required.";}

    // 3. Process if Valid
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO student_program (stakeholder_id, reg_no, ugpg, program, branch, regulation) VALUES (?, ?, ?, ?, ?, ?)");
        // ssssss refers to the data types being bound: i(int), s(string), s(string), s(string), s(string), s(string)
        $stmt->bind_param("isssss", $stakeholder_id, $reg_no, $ugpg, $program, $branch, $regulation);
        
        $stakeholder_id = $_SESSION['stakeholder_id'];
        $reg_no = strtoupper($form_data['reg_no']); 
        $ugpg = $form_data['ugpg'];
        $program = $form_data['program'];
        $branch = $form_data['branch'];
        $regulation = $form_data['regulation'];
        
        if ($stmt->execute()) {
            $_SESSION['reg_no'] = $reg_no; // Store Reg No for the next form
            // Redirect to Feedback Questionnaire (Step 37)
            header("Location: feedback_form.php");
            exit();
        } else {
            // Handle case where Register No might already exist (unique constraint failure)
            $errors['db'] = "Database error or Register Number already exists: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Program Details</title>
    <link rel="stylesheet" href="style.css">
    <script>
    // JavaScript data structures based on PHP data
    const programData = <?php echo json_encode($program_data); ?>;
    const branchData = <?php echo json_encode($branch_data); ?>;

    /**
     * Populates the Program dropdown based on the selected UG/PG type.
     */
    function updatePrograms() {
        const ugpg = document.getElementById('ugpg').value;
        const programSelect = document.getElementById('program');
        const selectedProgram = '<?php echo $form_data['program']; ?>'; // Get PHP pre-selected value

        // Reset dropdowns
        programSelect.innerHTML = '<option value="">Select</option>';
        updateBranches(null); // Clear branch dropdown immediately

        if (ugpg && programData[ugpg]) {
            programData[ugpg].forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                // Pre-select if it matches the previous submission value
                if (program === selectedProgram) {
                    option.selected = true;
                }
                programSelect.appendChild(option);
            });
        }

        // Crucial: If a program was pre-selected, trigger the branch update
        if (selectedProgram) {
            // Pass the pre-selected program to ensure the branch update works
            updateBranches(selectedProgram);
        }
    }

    /**
     * Populates the Branch dropdown based on the selected Program.
     */
    function updateBranches(programValue = null) {
        // Use the passed value (from page load logic) or the current dropdown value (from user change)
        const program = programValue || document.getElementById('program').value;
        const branchSelect = document.getElementById('branch');
        const selectedBranch = '<?php echo $form_data['branch']; ?>'; // Get PHP pre-selected value

        // Reset branch dropdown
        branchSelect.innerHTML = '<option value="">Select</option>';

        if (program && branchData[program]) {
            branchData[program].forEach(branch => {
                const option = document.createElement('option');
                option.value = branch;
                option.textContent = branch;
                // Pre-select if it matches the previous submission value
                if (branch === selectedBranch) {
                    option.selected = true;
                }
                branchSelect.appendChild(option);
            });
        }
    }

    // Execution Logic on Page Load
    document.addEventListener('DOMContentLoaded', () => {
        // Set up event listeners for user interaction
        document.getElementById('ugpg').addEventListener('change', updatePrograms);
        document.getElementById('program').addEventListener('change', () => updateBranches());

        // Logic to REPOPULATE dropdowns if form submission failed (preserves user input)
        const selectedUGPG = '<?php echo $form_data['ugpg']; ?>';
        if (selectedUGPG) {
            // If UG/PG was selected, we MUST call updatePrograms() 
            // to populate the Program dropdown and subsequently the Branch dropdown.
            updatePrograms();
        }
    });
    </script>
</head>

<body>

    <div class="container">
        <h2>STUDENT DETAILS</h2>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <div class="form-group">
                <label for="reg_no">Register No</label>
                <input type="text" id="reg_no" name="reg_no"
                    value="<?php echo htmlspecialchars($form_data['reg_no']); ?>" required>
                <div class="error"><?php echo $errors['reg_no'] ?? ''; ?></div>
            </div>

            <h3>PROGRAMME DETAILS</h3>

            <div class="form-group">
                <label>Select</label>
                <select id="ugpg" name="ugpg" required>
                    <option value="">Select</option>
                    <option value="UG" <?php echo $form_data['ugpg'] === 'UG' ? 'selected' : ''; ?>>UG</option>
                    <option value="PG" <?php echo $form_data['ugpg'] === 'PG' ? 'selected' : ''; ?>>PG</option>
                </select>
                <div class="error"><?php echo $errors['ugpg'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label>Program</label>
                <select id="program" name="program" required>
                    <option value="">Select</option>
                </select>
                <div class="error"><?php echo $errors['program'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label>Branch</label>
                <select id="branch" name="branch" required>
                    <option value="">Select</option>
                </select>
                <div class="error"><?php echo $errors['branch'] ?? ''; ?></div>
            </div>

            <div class="form-group">
                <label>Regulation</label>
                <select id="regulation" name="regulation" required>
                    <option value="">Select</option>
                    <option value="R19" <?php echo $form_data['regulation'] === 'R19' ? 'selected' : ''; ?>>R19</option>
                    <option value="R22" <?php echo $form_data['regulation'] === 'R22' ? 'selected' : ''; ?>>R22</option>
                </select>
                <div class="error"><?php echo $errors['regulation'] ?? ''; ?></div>
            </div>

            <button type="submit" class="submit-btn">SUBMIT</button>
            <div class="error"><?php echo $errors['db'] ?? ''; ?></div>
        </form>
    </div>

</body>

</html>