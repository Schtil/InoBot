<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>ТОП</title>
    <link rel="stylesheet" type="text/css" href="view.css" media="all">
    <script type="text/javascript" src="calendar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const getSort = ({ target }) => {
                const order = (target.dataset.order = -(target.dataset.order || -1));
                const index = [...target.parentNode.cells].indexOf(target);
                const collator = new Intl.Collator(['en', 'ru'], { numeric: true });
                const comparator = (index, order) => (a, b) => order * collator.compare(
                    a.children[index].innerHTML,
                    b.children[index].innerHTML
                );

                for(const tBody of target.closest('table').tBodies)
                    tBody.append(...[...tBody.rows].sort(comparator(index, order)));

                for(const cell of target.parentNode.cells)
                    cell.classList.toggle('sorted', cell === target);
            };

            document.querySelectorAll('.table_blur thead').forEach(tableTH => tableTH.addEventListener('click', () => getSort(event)));

        });
    </script>
</head>

<?php
include 'rb.php';

R::setup( 'mysql:host='.ENV("MYSQL_HOST", "localhost").';dbname='.ENV("MYSQL_DATABASE", "InoBot").'', ENV("MYSQL_USERNAME", "InoBot") , ENV("MYSQL_PASSWORD", "InoBot") );

$collection = R::findCollection('users', 'ORDER BY credits DESC ');


function ENV($index, $default = NULL)
{
    $file = file_get_contents(".env");
    $file = explode(PHP_EOL, $file);
    $params = [];
    foreach($file as $item)
    {
        $item = explode("=", $item);
        $params[trim($item[0])] = trim($item[1]);
    }
    if(isset($params[$index])) {
        return $params[$index];
    }
    return $default;
}
?>
<body>
<?php
if(isset($_GET["submit"])) {
    $startDate = $_GET["element_1_2"].".".$_GET["element_1_1"].".".$_GET["element_1_3"];
    $endDate = $_GET["element_2_2"].".".$_GET["element_2_1"].".".$_GET["element_2_3"];
    echo "<h1>ТОП C ".$startDate." ПО ".$endDate."</h1>";
}
else {
    echo "<h1>ТОП за всё время</h1>";
}
?>

    <form id="form_28087" class="appnitro"  method="get" action="">
        <ul >
            <li id="li_1" >
                <label class="description" for="element_1">Выбрать c/по</label>
                <span>
			<input id="element_1_2" name="element_1_2" class="element text" size="2" maxlength="2" value="" type="text"> /
			<label for="element_1_2">DD</label>
		</span>
                <span>
			<input id="element_1_1" name="element_1_1" class="element text" size="2" maxlength="2" value="" type="text"> /
			<label for="element_1_1">MM</label>
		</span>
                <span>
	 		<input id="element_1_3" name="element_1_3" class="element text" size="4" maxlength="4" value="" type="text">
			<label for="element_1_3">YYYY</label>
		</span>

                <span id="calendar_1">
			<img id="cal_img_1" class="datepicker" src="calendar.gif" alt="Pick a date.">
		</span>
                <script type="text/javascript">
                    Calendar.setup({
                        inputField	 : "element_1_3",
                        baseField    : "element_1",
                        displayArea  : "calendar_1",
                        button		 : "cal_img_1",
                        ifFormat	 : "%B %E, %Y",
                        onSelect	 : selectDate
                    });
                </script>

                <span>
			<input id="element_2_2" name="element_2_2" class="element text" size="2" maxlength="2" value="" type="text"> /
			<label for="element_2_2">DD</label>
		</span>
                <span>
			<input id="element_2_1" name="element_2_1" class="element text" size="2" maxlength="2" value="" type="text"> /
			<label for="element_2_1">MM</label>
		</span>
                <span>
	 		<input id="element_2_3" name="element_2_3" class="element text" size="4" maxlength="4" value="" type="text">
			<label for="element_2_3">YYYY</label>
		</span>

                <span id="calendar_2">
			<img id="cal_img_2" class="datepicker" src="calendar.gif" alt="Pick a date.">
		</span>
                <script type="text/javascript">
                    Calendar.setup({
                        inputField	 : "element_2_3",
                        baseField    : "element_2",
                        displayArea  : "calendar_2",
                        button		 : "cal_img_2",
                        ifFormat	 : "%Y/%m/%d",
                        onSelect	 : selectDate
                    });
                </script>

            </li>

            <li class="buttons">
                <input id="saveForm" class="button_text" type="submit" name="submit" value="Показать" />
            </li>
        </ul>
    </form>

    <table class="table_blur">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Получено</th>
            <th>Отправлено</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while($user = $collection->next()) {
            if($user->team_id != ENV("TEAM_WEB", "T024P5NRM9T")) {
                continue;
            }
            if(!isset($_GET["submit"])) {
                $numPush = R::count('log', ' author_slack_id = ? AND created_at > '.date("Y"), [$user->slack_id]);
                $numGet = R::count('log', ' from_slack_id = ? ', [$user->slack_id]);
            } else {
                $startDay = ($_GET["element_1_2"] < 10) ? "0".$_GET["element_1_2"] : "".$_GET["element_1_2"];
                $endDay = ($_GET["element_2_2"] < 10) ? "0".$_GET["element_2_2"] : "".$_GET["element_2_2"];
                $startDate = $_GET["element_1_3"]."-".$_GET["element_1_1"]."-".$startDay." 00:00:00";
                $endDate = $_GET["element_2_3"]."-".$_GET["element_2_1"]."-".$endDay." 23:59:59";
                $numPush = R::count('log', ' author_slack_id = ? AND created_at > ? AND created_at < ?', [$user->slack_id, $startDate, $endDate]);
                $numGet = R::count('log', ' from_slack_id = ? AND created_at > ? AND created_at < ?', [$user->slack_id, $startDate, $endDate]);
            }
            echo "<tr>"
                ."<td>".$user->name."</td>"
                ."<td>".$user->email."</td>"
                ."<td>".$numGet."</td>"
                ."<td>".$numPush."</td>";

            };
        ?>
        </tbody>
    </table>
</body>
</html>