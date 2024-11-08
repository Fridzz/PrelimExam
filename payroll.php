<?php
session_start(); // Start the session to store data across steps

// Handle Step 1: Employee Info Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_info'])) {
    $_SESSION['first_name'] = htmlspecialchars($_POST['first_name']);
    $_SESSION['last_name'] = htmlspecialchars($_POST['last_name']);
    $_SESSION['gender'] = $_POST['gender'];
    $_SESSION['course'] = $_POST['course'];
    $_SESSION['employment_type'] = $_POST['employment_type'];
    header('Location: payroll.php?step=2'); // Redirect to step 2
    exit();
}

// Handle Step 2: Working Hours & Cash Advance Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_hours'])) {
    $_SESSION['hours_worked'] = intval($_POST['hours_worked']);
    $_SESSION['cash_advance'] = floatval($_POST['cash_advance']);
    header('Location: payroll.php?step=3'); // Redirect to step 3
    exit();
}

// Handle Step 4: Complete Probationary
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_probationary'])) {
    session_destroy(); // Destroy the session to reset all data
    header('Location: payroll.php?step=1'); // Redirect to step 1 after completion
    exit();
}

// Handle Reset Button (available in Steps 3 and 4)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    session_destroy(); // Destroy the session to reset all data
    header('Location: payroll.php?step=1'); // Redirect to step 1
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll System</title>
</head>
<body>

<div>
    <?php
    // Determine the current step
    $step = isset($_GET['step']) ? $_GET['step'] : 1;

    // Step 1: Employee Info Form
    if ($step == 1) {
    ?>
        <h1>Step 1: Employee Information</h1>
        <form method="POST">
            <label>First Name:</label>
            <input type="text" name="first_name" required><br>

            <label>Last Name:</label>
            <input type="text" name="last_name" required><br>

            <label>Gender:</label>
            <input type="radio" name="gender" value="male" required> Male
            <input type="radio" name="gender" value="female" required> Female<br>

            <label>Select Destination:</label>
            <select name="course" required>
                <option value="">Select a Destination</option>
                <option value="Manager">Manager</option>
                <option value="Supervisor">Supervisor</option>
                <option value="Crew">Crew</option>
            </select><br>

            <label>Select Employment Type:</label>
            <select name="employment_type" required>
                <option value="">Select Employment Type</option>
                <option value="Regular">Regular</option>
                <option value="Probationary">Probationary</option>
            </select><br>

            <button type="submit" name="submit_info">Next</button>
        </form>

    <?php
    }

    // Step 2: Working Hours & Cash Advance Form
    elseif ($step == 2) {
        if (isset($_SESSION['gender'])) {
            $greeting = ($_SESSION['gender'] === 'male') ? "Hi Mr. {$_SESSION['first_name']} {$_SESSION['last_name']}" : "Hi Ms. {$_SESSION['first_name']} {$_SESSION['last_name']}";
            $rate = 200;
            echo "<p>$greeting, your rate is: ₱$rate</p>";
        }
    ?>
        <h1>Step 2: Working Hours & Cash Advance</h1>
        <form method="POST">
            <label>Regular Hours Worked:</label>
            <input type="number" name="hours_worked" value="0" min="0" required><br>

            <label>Cash Advance:</label>
            <input type="number" name="cash_advance" value="0" min="0" required><br>

            <button type="submit" name="submit_hours">Next</button>
        </form>
    <?php
    }

    // Step 3: Payroll Summary
    elseif ($step == 3) {
        if (
            isset($_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['hours_worked'], $_SESSION['cash_advance'], $_SESSION['employment_type'])
        ) {
            $first_name = htmlspecialchars($_SESSION['first_name']);
            $last_name = htmlspecialchars($_SESSION['last_name']);
            $hours_worked = intval($_SESSION['hours_worked']);
            $cash_advance = floatval($_SESSION['cash_advance']);
            $employment_type = $_SESSION['employment_type'];

            $regular_hourly_rate = 100;
            $gross_pay = $hours_worked * $regular_hourly_rate;

            $sss_deduction = 0;
            $tax_deduction = 0;
            $pagibig_deduction = 0;

            if ($employment_type === "Regular") {
                $sss_deduction = 0.10 * $gross_pay;
                $tax_deduction = 0.1212 * $gross_pay;
                $pagibig_deduction = 100;
            }

            $total_deductions = $sss_deduction + $tax_deduction + $pagibig_deduction + $cash_advance;
            $net_salary = $gross_pay - $total_deductions;

            if ($employment_type === "Probationary") {
                header('Location: payroll.php?step=4');
                exit();
            }
            ?>
            <h1>Payroll Summary</h1>
            <p><strong>Employee Name:</strong> <?php echo "$first_name $last_name"; ?></p>
            <p><strong>Gross Pay:</strong> ₱<?php echo number_format($gross_pay, 2); ?></p>
            <p><strong>Deductions:</strong></p>
            <ul>
                <li><strong>SSS:</strong> ₱<?php echo number_format($sss_deduction, 2); ?></li>
                <li><strong>TAX:</strong> ₱<?php echo number_format($tax_deduction, 2); ?></li>
                <li><strong>PAG-IBIG:</strong> ₱<?php echo number_format($pagibig_deduction, 2); ?></li>
                <li><strong>Cash Advance:</strong> ₱<?php echo number_format($cash_advance, 2); ?></li>
            </ul>
            <p><strong>Total Deductions:</strong> ₱<?php echo number_format($total_deductions, 2); ?></p>
            <p><strong>Net Salary:</strong> ₱<?php echo number_format($net_salary, 2); ?></p>

            <form method="POST">
                <button type="submit" name="reset">Reset</button>
            </form>
        <?php
        } else {
            header('Location: payroll.php?step=1');
            exit();
        }
    }

    // Step 4: Final Step for Probationary Employees
    elseif ($step == 4) {
        if (
            isset($_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['gender'], $_SESSION['hours_worked'], $_SESSION['cash_advance'], $_SESSION['employment_type'])
        ) {
            $first_name = htmlspecialchars($_SESSION['first_name']);
            $last_name = htmlspecialchars($_SESSION['last_name']);
            $gender = $_SESSION['gender'];
            $cash_advance = floatval($_SESSION['cash_advance']);
            $hours_worked = intval($_SESSION['hours_worked']);

            $regular_hourly_rate = 100;
            $gross_pay = $hours_worked * $regular_hourly_rate;
            $net_pay = $gross_pay - $cash_advance;

            $salutation = ($gender === 'male') ? "Mr." : "Ms.";

            ?>
            <h1>Step 4: Probationary Final Summary</h1>
            <p>Hi <?php echo "$salutation $first_name $last_name"; ?></p>
            <p><strong>Cash Advance:</strong> ₱<?php echo number_format($cash_advance, 2); ?></p>
            <p><strong>Net Pay:</strong> ₱<?php echo number_format($net_pay, 2); ?></p>

            <form method="POST">
                <button type="submit" name="reset">Reset</button>
            </form>
        <?php
        } else {
            header('Location: payroll.php?step=1');
            exit();
        }
    }
    ?>
</div>

</body>
</html>
