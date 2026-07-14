<?php
require_once "db_config.php";
require_once "vendor/dompdf/autoload.inc.php"; // Path to dompdf autoloader
use Dompdf\Dompdf;

$memberID = $_GET['MemberID'] ?? "";
$start = $_GET['start'] ?? "";

// Fetch individual order details for the member
$sql = "SELECT mo.OrderID, mo.OrderDate, gi.Description, mo.Quantity, mo.TotalPrice 
        FROM tblmemberorders mo
        INNER JOIN tblgroceryitems gi ON mo.ItemID = gi.ItemID
        WHERE mo.MemberID = ? AND mo.OrderDate >= ? AND mo.OrderDate <= DATE_ADD(?, INTERVAL 7 DAY)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $memberID, $start, $start);
$stmt->execute();
$result = $stmt->get_result();

// Build HTML content for the PDF
$html = "<h1>Order Summary: $memberID</h1><table border='1'><tr><th>Order ID</th><th>Date</th><th>Item</th><th>Qty</th><th>Price</th></tr>";
while($row = $result->fetch_assoc()) {
    $html .= "<tr><td>{$row['OrderID']}</td><td>{$row['OrderDate']}</td><td>{$row['Description']}</td><td>{$row['Quantity']}</td><td>{$row['TotalPrice']}</td></tr>";
}
$html .= "</table>";

// Initialize Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the PDF to the browser
$dompdf->stream("Order_Summary_$memberID.pdf");
?>
