<?php

class ReleaseBill {

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
        $this->patientName = preg_replace('/([a-z])([A-Z])/s','$1 $2', $patientName);
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
                    if($this->sendEmailToPatient() && $this->sendEmailToDoctor($this->doctorEmail)) {
                        $this->success = "Diagnosis emails were sent to you and the patient you named!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                        return false;
                    }
                case 2:
                    if($this->sendEmailToPatient()) {
                        $this->success = "A diagnosis confirmation email was sent to the patient!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending the patient's confirmation email. Try again soon.";
                        return false;
                    }
                case 3:
                    if($this->sendEmailToDoctor($this->doctorEmail)) {
                        $this->success = "You were sent a confirmation email regarding this diagnosis!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending your confirmation email. Try again soon.";
                        return false;
                    }
                case 4:
                    $this->success = "Diagnosis saved!";
                    return true;
                default:
                    die("An internal error occurred.");
            }
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
    
    
}