<?php

class AppointmentTableBuilder {

    function showAppointments($userProfile, $db) {
        switch($userProfile['user_type_id']) {
            case 3: // nurse, therefore having appointment with patient
                $userType = "nurse";
                $appointmentWith = "patient";
                break;
            case 2: // doctor, therefore having appointment with patient
                $userType = "doctor";
                $appointmentWith = "patient";
                break;
            case 1: // patient, therefore having appointment with doctor
                $userType = "patient";
                $appointmentWith = "doctor";
                break;
        }
        $query = "
                SELECT *
                FROM appointment
                WHERE "
            . $userType . "_email = :" . $userType . "Email";
        $query_params = array(
            ":" . $userType . "Email" => $userProfile["email"]
        );
        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        if ($stmt->rowCount() > 0) {
            echo '<table border="1" style="width:100%">';
            if ($appointmentWith == "doctor") {
                $upCase = "Doctor";
            } else {
                $upCase = "Patient";
            }
            if ($userType == "doctor") {
                echo '<tr><td>' . $upCase . ' Name</td><td>Date</td><td>Time</td><td>Nurse Name</td>'
                    . '<td>Diagnose</td><td>Cancel</td></tr>';
            } else {
                echo '<tr><td>' . $upCase . ' Name</td><td>Date</td><td>Time</td><td>Nurse Name</td>'
                    . '<td>Reschedule</td><td>Cancel</td></tr>';
            }
            // Loop over query from appointment table.
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Need to query the users table to get the user ID we are looking for
                // with the link to the user profile page.
                $query2 = "
                       SELECT *
                       FROM users
                       WHERE
                           first_name = :appointmentWithFirstName
                           AND
                           last_name = :appointmentWithLastName
                           AND
                           user_type_id = :user_type
                        ";
                $name = explode(" ", $row[$appointmentWith . "_name"]);
                $query_params2 = array(
                    ":appointmentWithFirstName" => $name[0],
                    ":appointmentWithLastName" => $name[1],
                    ":user_type" => "2"
                );
                try {
                    $stmt2 = $db->prepare($query2);
                    $result2 = $stmt2->execute($query_params2);
                } catch(PDOException $ex) {
                    die("Failed to run query: " . $ex->getMessage());
                }
                $entry2 = $stmt2->fetch();
                $query3 = "
                       SELECT *
                       FROM users
                       WHERE
                           first_name = :nurseFirstName
                           AND
                           last_name = :nurseLastName
                           AND
                           user_type_id = :user_type
                        ";
                $name = explode(" ", $row["nurse_name"]);
                $query_params3 = array(
                    ":nurseFirstName" => $name[0],
                    ":nurseLastName" => $name[1],
                    ":user_type" => "3"
                );
                try {
                    $stmt3 = $db->prepare($query3);
                    $result3 = $stmt3->execute($query_params3);
                } catch(PDOException $ex) {
                    die("Failed to run query: " . $ex->getMessage());
                }
                $entry3 = $stmt3->fetch();
                $link2 = "http://wal-engproject.rhcloud.com/src/user_page.php?id=" . $entry2['id'];
                $link3 = "http://wal-engproject.rhcloud.com/src/user_page.php?id=" . $entry3['id'];

                if ($userType == "doctor") {
                    echo "<tr><td><a href=\"" . $link2 . "\">" . $row[$appointmentWith . "_name"] . "</a></td>"
                        . "<td>" . $row["date"] . "</td><td>" . $row["time"] . "</td><td><a href=\""
                        . $link3 . "\">" . $row["nurse_name"] . "</td><td><a href=\"diagnosis.php?id=" . $row['id']
                        . "\">Diagnose</a></td><td><a href=\"cancel_appointment.php?id=". $row['id']
                        . "\">Cancel</a></td></tr>";
                } else {
                    echo "<tr><td><a href=\"" . $link2 . "\">" . $row[$appointmentWith . "_name"] . "</a></td>"
                        . "<td>" . $row["date"] . "</td><td>" . $row["time"] . "</td><td><a href=\""
                        . $link3 . "\">" . $row["nurse_name"] . "</td><td><a href=\"reschedule_appointment.php?id=" . $row['id']
                        . "&date=" . $row['date'] . "\">Reschedule</a></td><td><a href=\"cancel_appointment.php?id=". $row['id']
                        . "\">Cancel</a></td></tr>";
                }
            }
            echo '</table><br/>';
            if($stmt->rowCount() == 1) {
                echo "Click on the " . $appointmentWith . "'s  or nurse's name to learn more information about them.";
            } else if ($stmt->rowCount() > 1) {
                echo "Click on the " . $appointmentWith . "s'  or nurses' name to learn more information about them.";
            }
        } else {
            echo "You currently have no current appointments scheduled.";
        }
    }

}