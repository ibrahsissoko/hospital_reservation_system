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
            if($_SESSION['user']['appointment_confirm_email'] == "Yes" || $_SESSION['user']['appointment_confirm_email'] == NULL) {
                if($this->doctorInfo['appointment_confirm_email'] == "Yes" || $this->doctorInfo['appointment_confirm_email'] == NULL) {
                    if($this->sendEmailToPatient() && $this->sendEmailToDoctor()) {
                        $this->updateAppointmentTable();
                        $this->success = "Confirmation emails were sent to you and the doctor you requested!";
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                    } 
                } else {
                    if($this->sendEmailToPatient()) {
                        $this->updateAppointmentTable();
                        $this->success = "A confirmation email was sent to you regarding your appointment.";
                    } else {
                        $this->error = "An error occurred sending you a confirmation email. Try again soon.";
                    }
                }
            } else {
                if($this->doctorInfo['appointment_confirm_email'] == "Yes" || $this->doctorInfo['appointment_confirm_email'] == NULL) {
                    if($this->sendEmailToDoctor()) {
                        $this->updateAppointmentTable();
                        $this->success = "Appointment booked!";
                    } else {
                        $this->error = "Appointment could not be booked. Try again soon.";
                    }
                } else {
                    $this->updateAppointmentTable();
                    $this->success = "Appointment booked!";
                }
            }
        }
    }
    
    function assignNurse() {
        $query = "
                SELECT *
                FROM users
                WHERE
                    email =" . $this->doctorEmail;
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute();
        } catch(PDOException $e) {
            die("Failed to update tables. (1) " . $e->getMessage());
        }
        $row = $stmt->fetch();
        $query = "
                SELECT *
                FROM users
                WHERE
                    shift_id =" . $row["shift_id"] .
                "AND
                    user_type_id = 3
                AND
                    department_id =" . $row["department_id"]
            ;
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute();
        } catch(PDOException $e) {
            die("Failed to update tables. (2) " . $e->getMessage());
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
                                    nurse_email = " . $row['email'] .
                                "AND
                                    date = $this->date
                                AND
                                    time = $this->time
                                ";
                        try {
                            $stmt2 = $this->db->prepare($query2);
                            $result = $stmt2->execute();
                        } catch(PDOException $e) {
                            die("Failed to update tables. (3) " . $e->getMessage());
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
    
    function sendEmailToPatient() {
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($this->patientEmail);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Appointment Confirmation";
        $mail->Body    = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently scheduled an appointment with ' . $this->doctorName
                . ' on ' . $this->date . ' at ' . $this->time . '. The nurse assigned for '
                . 'this apointment is ' . $this->nurseName . '. If you need to reschedule'
                . ' or cancle your appointment, login to your account, view your appointments, '
                . 'and click "cancle appointment".<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
    
    function sendEmailToDoctor() {
    
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($this->doctorEmail);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Appointment Confirmation";
        $mail->Body    = 'Hello!<br/><br/>'
                . $this->patientName . ' requested an appointment with you on '
                . $this->date . ' at ' . $this->time . '. Your nuse will be ' . $this->nurseName 
                . '.<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
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