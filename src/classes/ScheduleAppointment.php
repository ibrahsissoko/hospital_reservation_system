<?php

class ScheduleAppointment {

    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    public $doctorInfo;
    private $nurseEmail;
    private $nurseName;
    private $nurseInfo;
    private $nurseId;
    private $date;
    private $time;
    private $db;
    public $success;
    public $error;
    
    function __construct($doctorName, $patientName, $patientEmail, $date, $time, $db) {
        $this->doctorName = $doctorName;
        $this->patientName = $patientName;
        $this->patientEmail = $patientEmail;
        $this->db  = $db;
        if (!empty($date) || !empty($time)) {
            $this->time = $time;
            $this->date = $date;

            $query = "SELECT * FROM users WHERE user_type_id=2";
            try {
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Currently assuming no doctors will have the same first name, last
                    // name, and degree.
                    $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"] . $row["degree"]);
                    $string2 = str_replace(' ', '', $doctorName);
                    if(strcmp($string1, $string2) == 0) {
                        $this->doctorInfo = $row;
                        $this->doctorEmail = $this->doctorInfo["email"];
                        break;
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather doctor's email address.");
            }
            if (empty($this->doctorEmail)) {
                $this->error = "An internal error occurred acquiring the doctor's information.";
            }
        } else {
            $this->error = "Please fill out all fields.";
        }
    }
    
    function initiate($_SESSION) {
        if (empty($this->error)) {
            $this->assignNurse();
            $this->updatePayoutTable();
            $emailPatient = ($_SESSION['user']['appointment_confirm_email'] == "Yes" || $_SESSION['user']['appointment_confirm_email'] == NULL);
            $emailDoctor = ($this->doctorInfo['appointment_confirm_email'] == "Yes" || $this->doctorInfo['appointment_confirm_email'] == NULL);
            $emailNurse = ($this->nurseInfo['appointment_confirm_email'] == "Yes" || $this->nurseInfo['appointment_confirm_email'] == NULL);
            if ($emailPatient && $emailDoctor && $emailNurse) {
                $option = 1;
            } else if ($emailPatient && $emailDoctor && !$emailNurse) {
                $option = 2;
            } else if ($emailPatient && !$emailDoctor && !$emailNurse) {
                $option = 3;
            } else if (!$emailPatient && !$emailDoctor && !$emailNurse) {
                $option = 4;
            } else if (!$emailPatient && $emailDoctor && !$emailNurse) {
                $option = 5;
            } else if (!$emailPatient && !$emailDoctor && $emailNurse) {
                $option = 6;
            } else if (!$emailPatient && $emailDoctor && $emailNurse) {
                $option = 7;
            } else if ($emailPatient && !$emailDoctor && $emailNurse) {
                $option = 8;
            }

            switch($option) {
                case 1:
                    if($this->sendEmailToPatient() && $this->sendEmailToDoctor() && $this->sendEmailToNurse()) {
                        $this->updateAppointmentTable();
                        $this->success = "You have been sent a confirmation email for your appointment.";
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                    }
                    break;
                case 2:
                    if($this->sendEmailToPatient() && $this->sendEmailToDoctor()) {
                        $this->updateAppointmentTable();
                        $this->success = "You have been sent a confirmation email for your appointment.";
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                    }
                    break;
                case 3:
                    if($this->sendEmailToPatient()) {
                        $this->updateAppointmentTable();
                        $this->success = "You have been sent a confirmation email for your appointment.";
                    } else {
                        $this->error = "An error occurred sending your confirmation email. Try again soon.";
                    }
                    break;
                case 4:
                    $this->updateAppointmentTable();
                    $this->success = "Appointment booked.";
                    break;
                case 5:
                    if($this->sendEmailToDoctor()) {
                        $this->updateAppointmentTable();
                        $this->success = "Appointment booked.";
                    } else {
                        $this->error = "An error occurred. Try again soon.";
                    }
                    break;
                case 6:
                    if($this->sendEmailToNurse()) {
                        $this->updateAppointmentTable();
                        $this->success = "Appointment booked.";
                    } else {
                        $this->error = "An error occurred. Try again soon.";
                    }
                    break;
                case 7:
                    if($this->sendEmailToDoctor() && $this->sendEmailToNurse()) {
                        $this->updateAppointmentTable();
                        $this->success = "Appointment booked.";
                    } else {
                        $this->error = "An error occurred. Try again soon.";
                    }
                    break;
                case 8:
                    if($this->sendEmailToPatient() && $this->sendEmailToNurse()) {
                        $this->updateAppointmentTable();
                        $this->success = "You have been sent a confirmation email for your appointment.";
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                    }
                    break;
                default:
                    die("An internal error occurred.");
            }
        }
    }
    
    function assignNurse() {
        $query = "
                SELECT *
                FROM users
                WHERE
                    email = :email
                ";
        $query_params = array(
            ':email' => $this->doctorInfo["email"]
        );
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update tables. " . $e->getMessage());
        }
        $row = $stmt->fetch();
        $query = "
                SELECT *
                FROM users
                WHERE
                    shift_id = " . $row["shift_id"] .
                " AND
                    user_type_id = 3
                AND
                    department_id = " . $row["department_id"]
            ;
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute();
        } catch(PDOException $e) {
            die("Failed to update tables. " . $e->getMessage());
        }
        if ($stmt->rowCount() != 0) {
            while(empty($this->nurseInfo)){
                $i = rand(0,10000) % $stmt->rowcount();
                $rowCount = 0;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if($i == $rowCount) {
                        $query2 = "
                                SELECT *
                                FROM appointment
                                WHERE
                                    nurse_email = :nurse_email
                                AND
                                    date = :date
                                AND
                                    time = :time
                                ";
                        $query_params2 = array(
                            ':nurse_email' => $row['email'],
                            ':date' => $this->date,
                            ':time' => $this->time
                        );
                        try {
                            $stmt2 = $this->db->prepare($query2);
                            $result = $stmt2->execute($query_params2);
                        } catch(PDOException $e) {
                            die("Failed to update tables. " . $e->getMessage());
                        }
                        if ($stmt2->rowCount() > 0) {
                            $nurseAlreadyScheduled = true;
                        } else {
                            $nurseAlreadyScheduled = false;
                        }
                        if(!$nurseAlreadyScheduled) {
                            $this->nurseInfo = $row;
                            break;
                        }
                    }
                    $rowCount++;
                }
            }
        }
        $this->nurseEmail = $this->nurseInfo['email'];
        $this->nurseName = $this->nurseInfo['first_name'] . " " . $this->nurseInfo['last_name'];
    }
    function updatePayoutTable(){
        $query2 = "
                SELECT *
                FROM users
                WHERE
                    email = :nurse_email
                ";
        $query_params2 = array(
            ':nurse_email' => $this->nurseEmail
        );
        try {
            $stmt2 = $this->db->prepare($query2);
            $stmt2->execute($query_params2);
        } catch(PDOException $e) {
            die("Failed to gather doctor information. " . $e->getMessage());
        }
        $nurseInfo2 = $stmt2->fetch(); 
        $this->$nurseId = $nurseInfo2['id'];
        $query = "
            SELECT * 
            FROM payout
            WHERE
                doctor_id = :doctor_id
            ";
        $query_params = array (
            ':doctor_id' => $this->nurseId
        );
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update payout table. " . $e->getMessage());
        }
        $query3 = "
                SELECT *
                FROM department
                WHERE
                    id = :department_id
                ";
        $query_params3 = array(
            ':department_id' => $nurseInfo2['department_id']
        );
        try {
            $stmt3 = $this->db->prepare($query3);
            $stmt3->execute($query_params3);
        } catch(PDOException $e) {
            die("Failed to gather department information. " . $e->getMessage());
        }
        $departmentInfo = $stmt3->fetch();
        $amount_due = 40 + (intval($nurseInfo2['years_of_experience'])/2)*2;
        if ($amount_due > 100) {
            $amount_due = 100;
        }
        $amount_due *= floatval($departmentInfo['pay_scaling_factor']);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $amount_due += intval($row['amount_due']);
            $query4 = "
                    UPDATE payout
                    SET
                        amount_due = :amount_due,
                        date = :date_object
                    WHERE
                        doctor_id = :doctor_id
                    ";
            $query_params4 = array(
                ':amount_due' => $amount_due,
                ':date_object' => date("m/d/y"),
                ':doctor_id' => $nurseInfo2['id']
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
                ':doctor_id' => $this->nurseId,
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

    function sendEmailToPatient() {
        $message = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently scheduled an appointment with ' . $this->doctorName
                . ' on ' . $this->date . ' at ' . $this->time . '. The nurse assigned for '
                . 'this apointment is ' . $this->nurseName . '. If you need to reschedule'
                . ' or cancel your appointment, login to your account, view your appointments, '
                . 'and click "cancel appointment".<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($this->patientEmail,"Appointment Confirmation",$message,false);
    }
    
    function sendEmailToDoctor() {
        $message = 'Hello!<br/><br/>'
                . $this->patientName . ' requested an appointment with you on '
                . $this->date . ' at ' . $this->time . '. Your nurse will be ' . $this->nurseName 
                . '.<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($this->doctorEmail,"Appointment Confirmation",$message,false);
    }
    
    function sendEmailToNurse() {
        $message = 'Hello!<br/><br/>'
                . $this->patientName . ' requested an appointment with you on '
                . $this->date . ' at ' . $this->time . '. The doctor will be ' . $this->doctorName 
                . '.<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($this->nurseEmail,"Appointment Confirmation",$message,false);
    }
    
    function updateAppointmentTable() {
        $query = "
                    INSERT INTO appointment (
                        date,
                        time,
                        patient_name,
                        patient_email,
                        doctor_name,
                        doctor_email,
                        nurse_name,
                        nurse_email
                    ) VALUES (
                        :date,
                        :time,
                        :patient_name,
                        :patient_email,
                        :doctor_name,
                        :doctor_email,
                        :nurse_name,
                        :nurse_email
                    )
                    ";
        $query_params = array(
            ':date' => $this->date,
            ':time' => $this->time,
            ':patient_name' => $this->patientName,
            ':patient_email' => $this->patientEmail,
            ':doctor_name' => $this->doctorName,
            ':doctor_email' => $this->doctorEmail,
            ':nurse_name' => $this->nurseName,
            ':nurse_email' => $this->nurseEmail
        );
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update tables. " + $e->getMessage());
        }
    }
}