<?php

class Diagnosis {

    
    public $session;
    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    private $drug_name;
    private $date;
    private $time;
    private $db;
    private $prescriptionID;
    public $patientInfo;
    private $diagnosis;
    private $observations;
    private $amount_due;
    public $success;
    public $error;

    function __construct($doctorName, $patientName, $doctorEmail, $diagnosis, $observations,$date,$time,$db,$medication) {
        $this->doctorName = preg_replace('/([a-z])([A-Z])/s','$1 $2', $doctorName);
        $this->patientName = $patientName;
        $this->doctorEmail = $doctorEmail;
        $this->db = $db;
        $this->date = $date;
        $this->time = $time;
        $this->amount_due = 500.00;
        if (!empty($diagnosis) || !empty($observations)) {
            $this->diagnosis = $diagnosis;
            $this->observations = $observations;
            $query = "SELECT * FROM users WHERE user_type_id=1";
            try {
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"]);
                    $string2 = str_replace(' ', '', $patientName);
                    if(strcmp($string1, $string2) == 0) {
                        $this->patientInfo = $row;
                        $this->patientEmail = $this->patientInfo["email"];
                        break;
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather patient's email address. " . $e->getMessage());
            }
            if (empty($this->patientEmail)) {
                $this->error = "An internal error occurred acquiring the doctor's information.";
            }
            if (!empty($medication) && $medication != "Medication") {
                $query = "SELECT * FROM prescription WHERE drug_name = :drug_name";
                $query_params = array(
                    ':drug_name' => $medication
                );
                try {
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($query_params);
                } catch(PDOException $e) {
                    die("Failed to gather prescription information. " . $e->getMessage());
                }
                $this->drug_name = $medication;
                $row = $stmt->fetch();
                $this->prescriptionID = $row['id'];
                $this->amount_due += intval($row['price']);
            } else {
                $this->prescriptionID = 0;
            }
        } else {
            $this->error = "Please fill out all fields.";
        }
    }
    
    function initiate($session, $appointmentId) {
        $this->session = $session;
        if(empty($this->error)){
            $emailPatient = ($this->patientInfo['diagnosis_confirm_email'] == "Yes" || $this->patientInfo['diagnosis_confirm_email'] == NULL);
            $emailDoctor = ($session['user']['diagnosis_confirm_email'] == "Yes" || $session['user']['diagnosis_confirm_email'] == NULL);
            if ($emailPatient && $emailDoctor) {
                $option = 1;
            } else if ($emailPatient && !$emailDoctor) {
                $option = 2;
            } else if (!$emailPatient && $emailDoctor) {
                $option = 3;
            } else if (!$emailPatient && !$emailDoctor) {
                $option = 4;
            }
            switch($option) {
                case 1:
                    $this->updateBillTable($appointmentId);
                    if($this->sendEmailToPatient() && $this->sendEmailToDoctor($session["user"]["email"])) {
                        $this->updateDiagnosisTable();
                        $this->updatePayoutTable();
                        $this->success = "Diagnosis emails were sent to you and the patient you named!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                        return false;
                    }
                case 2:
                    $this->updateBillTable($appointmentId);
                    if($this->sendEmailToPatient()) {
                        $this->updateDiagnosisTable();
                        $this->updatePayoutTable();
                        $this->success = "A diagnosis confirmation email was sent to the patient!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending the patient's confirmation email. Try again soon.";
                        return false;
                    }
                case 3:
                    $this->updateBillTable($appointmentId);
                    if($this->sendEmailToDoctor($session["user"]["email"])) {
                        $this->updateDiagnosisTable();
                        $this->updatePayoutTable();
                        $this->success = "You were sent a confirmation email regarding this diagnosis!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending your confirmation email. Try again soon.";
                        return false;
                    }
                case 4:
                    $this->updateBillTable($appointmentId);
                    $this->updateDiagnosisTable();
                    $this->updatePayoutTable();
                    $this->success = "Diagnosis saved!";
                    return true;
                default:
                    die("An internal error occurred.");
            }
        }
    }

    function updateBillTable($appId) {
        $query1 = " SELECT * FROM bill WHERE patient_email = :patient_email";
        $query_params1 = array(':patient_email'  => $this->patientEmail);
        try{
        $stmt1 = $this->db->prepare($query1);
        $result1 = $stmt1->execute($query_params1);
        } catch(PDOException $e) {
            die("Failed to update bill table. " . $e->getMessage());
        }
        if(!empty($result1)){
            try {
            while($row = $stmt1->fetch(PDO::FETCH_ASSOC)){
            $this->patientInfo = $row;
            $this->amount_due = ($this->patientInfo["amount_due"]) + $this->amount_due;
            $query2 = "UPDATE bill SET amount_due = :amount_due, original_due = :original_due WHERE patient_email = :patient_email";
            $query_params2 = array(':patient_email'  => $this->patientEmail,
                                    ':original_due' => $this->amount_due,
                                    ':amount_due'=>$this->amount_due);
            $stmt2 = $this->db->prepare($query2);
            $result2 = $stmt2->execute($query_params2);
            break;
            }}catch(PDOException $e) {
                die("Failed to gather patient's amount due.");
           }
                
        
        }
    
        else{      

            $query = "
                    INSERT INTO bill (
                        amount_due,
                        original_due,
                        patient_name,
                        patient_email,
                        doctor_name,
                        doctor_email,
                        appointment_id
                    ) VALUES (
                        :amount_due,
                        :original_due,
                        :patient_name,
                        :patient_email,
                        :doctor_name,
                        :doctor_email,
                        :app_id
                    )
                    ";    
            $query_params = array(
            ':amount_due' => $this->amount_due,
            ':original_due' => $this->amount_due,
            ':patient_name' => $this->patientName,
            ':patient_email' => $this->patientEmail,
            ':doctor_name' => $this->doctorName,
            ':doctor_email' => $this->doctorEmail,
            ':app_id' => $appId
        );
        try {
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $e) {
                die("Failed to update bill table. " . $e->getMessage());
            }
        }
    
    }
    
    function updatePayoutTable() {
        // Determine if this doctor already has an entry in the payout table.
        $query = "
            SELECT * 
            FROM payout
            WHERE
                doctor_id = :doctor_id
            ";
        $query_params = array (
            ':doctor_id' => $this->session['user']['id']
        );
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update payout table. " . $e->getMessage());
        }
        // Determine the amount due.
        $query2 = "
                SELECT *
                FROM users
                WHERE
                    id = :doctor_id
                ";
        $query_params2 = array(
            ':doctor_id' => $this->session['user']['id']
        );
        try {
            $stmt2 = $this->db->prepare($query2);
            $stmt2->execute($query_params2);
        } catch(PDOException $e) {
            die("Failed to gather doctor information. " . $e->getMessage());
        }
        $doctorInfo = $stmt2->fetch();

        $query3 = "
                SELECT *
                FROM department
                WHERE
                    id = :department_id
                ";
        $query_params3 = array(
            ':department_id' => $this->session['user']['department_id']
        );
        try {
            $stmt3 = $this->db->prepare($query3);
            $stmt3->execute($query_params3);
        } catch(PDOException $e) {
            die("Failed to gather department information. " . $e->getMessage());
        }
        $departmentInfo = $stmt3->fetch();
        $amount_due = 500 + (intval($doctorInfo['years_of_experience'])/2)*25;
        if ($amount_due > 1000) {
            $amount_due = 1000;
        }
        $amount_due *= floatval($departmentInfo['pay_scaling_factor']);
        if ($stmt->rowCont() > 0) {
            $row = $stmt->fetch();
            $amount_due += intval($row['amount_due']);
            $query4 = "
                    UPDATE payout
                    SET
                        amount_due = :amount_due
                        date = :date
                    WHERE
                        doctor_id = :doctor_id
                    ";    
            $query_params4 = array(
                ':amount_due' => $amount_due,
                ':date' => date("m/d/y"),
                ':doctor_id' => $this->session['user']['id']
            );
            try {
                $stmt4 = $this->db->prepare($query4);
                $stmt4->execute($query_params4);
            } catch(PDOException $e) {
                die("Failed to update payout table. " . $e->getMessage());
            }      
        } else {
            // Insert into the database as opposed to updating.
            $query4 = "
                    INSERT INTO payout (
                        doctor_id,
                        date,
                        amount_due
                    ) VALUES (
                        :doctor_id,
                        :date,
                        :amount_due
                    )";    
            $query_params4 = array(
                ':doctor_id' => $this->session['user']['id'],
                ':date' => date("m/d/y"),
                ':amount_due' => $amount_due
            );
            try {
                $stmt4 = $this->db->prepare($query4);
                $stmt4->execute($query_params4);
            } catch(PDOException $e) {
                die("Failed to insert values into payout table. " . $e->getMessage());
            }      
        }  
    }
    
    function updateDiagnosisTable(){
        
        $query = "
                INSERT INTO diagnosis (
                    observations,
                    diagnosis,
                    patient_name,
                    patient_email,
                    doctor_name,
                    doctor_email,
                    prescription_id,
                    date,
                    time,
                    amount_due
                ) VALUES (
                    :observations,
                    :diagnosis,
                    :patient_name,
                    :patient_email,
                    :doctor_name,
                    :doctor_email,
                    :prescription_id,
                    :date,
                    :time,
                    :amount_due
                )
                ";    
        $query_params = array(
        ':observations' => $this->observations,
        ':diagnosis' => $this->diagnosis,
        ':patient_name' => $this->patientName,
        ':patient_email' => $this->patientEmail,
        ':doctor_name' => $this->doctorName,
        ':doctor_email' => $this->doctorEmail,
        ':prescription_id' => $this->prescriptionID,
        ':date' => $this->date,
        ':time' => $this->time,
        ':amount_due' => $this->amount_due
    );
    try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update diagnosis table. " . $e->getMessage());
        }
    }
       
    function sendEmailToPatient() {
        $message = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently scheduled an appointment with ' . $this->doctorName
                . '. Here are some details of your appointment:'
                . '. Your observations by the doctor are: '. $this->observations
                . '. Your diagnosis by the doctor is: ' . $this->diagnosis
                . '. Your total is $'. $this->amount_due 
                . '. Attached is the official bill for the service'
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmailWithAttachment($this->prescriptionID,$this->db,$this->patientName,$this->doctorName,$this->observations,
                $this->diagnosis,$this->medication,$this->amount_due,$this->patientEmail,"Diagnosis and Billing",$message);
    }
    
    function sendEmailToDoctor($doctorEmail) {
        $message = 'Hello!<br/><br/>'
                . 'You recently had an appointment with ' . $this->patientName . '. Email of patient is: '
                . $this->patientEmail . '. Here is'
                . ' the receipt of the diagnosis form that you submitted: $' . $this->amount_due
                . '. Attached is the official bill for the service'
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmailWithAttachment($this->prescriptionID,$this->db,$this->patientName,$this->doctorName,$this->observations,
                $this->diagnosis,$this->medication,$this->amount_due,$doctorEmail,"Diagnosis and Billing",$message);
    }
    
    function updateAppointment($appointmentID) {
        $query = "
                UPDATE appointment
                SET
                    completed = :completed
                WHERE
                    id = :id
                ";    
        $query_params = array(
            ':completed' => 1,
            ':id' => $appointmentID
        );
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update appointment information. " . $e->getMessage());
        }
    }
}