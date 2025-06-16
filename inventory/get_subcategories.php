<?php
include('../includes/dbconfig.php'); // Adjust the path as needed

if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);

    $query = "SELECT Subcategory_ID, Subcategory_Name FROM subcategories WHERE Category_ID = '$category_id'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo '<option value="">Error fetching subcategories</option>';
        exit;
    }

    $output = '<option selected disabled value="">Select Subcategory</option>';
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<option value="' . $row['Subcategory_ID'] . '">' . $row['Subcategory_Name'] . '</option>';
        }
    } else {
        $output .= '<option value="">No Subcategories Available</option>';
    }
    echo $output;
} else {
    echo '<option value="">Invalid Category ID</option>';
}
?>