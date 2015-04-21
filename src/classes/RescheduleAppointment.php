<?php

class RescheduleAppointment {

    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    private $id;
    public $doctorInfo;
    private $nurseEmail;
    private $nurseName;
    private $nurseInfo;
    private $date;
    private $time;
    private $db;
    public $success;
    public $error;
    
    function __construct($appointmentInfo, $time, $date, $db) {
        $this->id = $appointmentInfo['id'];
        $this->doctorName = $appointmentInfo['doctor_name'];
        $this->doctorEmail = $appointmentInfo['doctor_email'];
        $this->patientName = $appointmentInfo['patient_name'];
        $this->patientEmail = $appointmentInfo['patient_email'];
        $this->nurseName = $appointmentInfo['nurse_name'];
        $this->nurseEmail = $appointmentInfo['nurse_email'];
        $this->time = $time;
        $this->date = $date;
        $this->db  = $db;

        $query = "SELECT * FROM users WHERE user_type_id=2";
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Currently assuming no doctors will have the same first name, last
                // name, and degree.
                $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"] . $row["degree"]);
                $string2 = str_replace(' ', '', $this->doctorName);
                if(strcmp($string1, $string2) == 0) {
                    $this->doctorInfo = $row;
                    break;
                }
            }
        } catch(PDOException $e) {
            die("Failed to gather doctor's email address.");
        }
        $query = "SELECT * FROM users WHERE user_type_id=3";
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Currently assuming no doctors will have the same first name, last
                // name, and degree.
                $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"]);
                $string2 = str_replace(' ', '', $this->nurseName);
                if(strcmp($string1, $string2) == 0) {
                    $this->nurseInfo = $row;
                    break;
                }
            }
        } catch(PDOException $e) {
            die("Failed to gather doctor's email address.");
        }
        if (empty($this->doctorEmail) || empty($this->nurseEmail)) {
            $this->error = "An internal error occurred acquiring hospital staff information.";
        }
    }
    
    function initiate($_SESSION) {
        if (empty($this->error)) {
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

            $this->sendEmailToAdmins();
        }
    }
    
    function sendEmailToPatient() {
        $message = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently rescheduled an appointment with ' . $this->doctorName
                . ' to ' . $this->date . ' at ' . $this->time . '. The nurse assigned for '
                . 'this apointment is ' . $this->nurseName . '. If you need to further reschedule'
                . ' or cancel your appointment, login to your account, view your appointments, '
                . 'and click "cancel appointment".<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($this->patientEmail,"Appointment Confirmation",$message,false);
    }
    
    function sendEmailToDoctor() {
        $message = 'Hello!<br/><br/>'
                . $this->patientName . ' requested an appointment reschedule with you on '
                . $this->date . ' at ' . $this->time . '. Your nurse will be ' . $this->nurseName 
                . '.<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($this->doctorEmail,"Appointment Confirmation",$message,false);
    }
    
    function sendEmailToNurse() {
        $message = 'Hello!<br/><br/>'
                . $this->patientName . ' requested an appointment reschedule with you on '
                . $this->date . ' at ' . $this->time . '. The doctor will be ' . $this->doctorName 
                . '.<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($this->nurseEmail,"Appointment Confirmation",$message,false);
    }

    function sendEmailToAdmins() {
        $message = $this->patientName . ' requested an appointment reschedule with ' . $this->doctorName . ' and ' . $this->nurseName . ' on '
            . $this->date . ' at ' . $this->time . '.';
        $email = new SendEmail();

        $query = "
                    SELECT *
                    FROM users
                    WHERE
                      user_type_id = :type_id
                    ";

        $query_params = array(
            ':type_id' => '4'
        );
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(Exception $ex) {

        }

        $to = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($to, $row['email']);
        }

        return $email->SendEmailToMultipleUsers($to,"Appointment Rescheduled",$message,false);
    }
    
    function updateAppointmentTable() {
        $query = "
                    UPDATE appointment
                    SET
                        date = :date,
                        time = :time
                    WHERE
                        id = :id
                    ";
        $query_params = array(
            ':date' => $this->date,
            ':time' => $this->time,
            ':id' => $this->id
        );
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update tables. " + $e->getMessage());
        }
    }
}