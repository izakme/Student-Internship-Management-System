require_once "../../backend/classes/Internship.php";

$internship = new Internship();

if(isset($_GET['search']))
{
    $result = $internship->searchInternships($_GET['search']);
}
else
{
    $result = $internship->getAllInternships();
}