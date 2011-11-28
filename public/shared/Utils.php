<?

//----------------------------------------------------------
// backtraceDump
//
// Returns backtrace as html table
// parameter is array of functions to not report on (lower case)
//----------------------------------------------------------
function backtraceDump($Ignore = array())

{

    $Ignore[] = "backtracedump";

    $Trace = "";

    $Backtrace = debug_backtrace();

    $Trace .='<table class="error" border=1>';
    $Trace .="<th>Filename</th>";
    $Trace .="<th>Line</th>";
    $Trace .="<th>Function</th>";
    $Trace .="<th>Args</th>";

    foreach($Backtrace as $Row) {

        if (in_array($Row["function"], $Ignore)) {
            continue;
        }

        $Trace .= "<tr>";
        $Trace .= "<td>" . basename($Row["file"]) . "</td>";
        $Trace .= "<td align=right>" . $Row["line"] . "</td>";

        if (!$Row["class"]) {
            $Trace .= "<td>" . $Row["function"] . "()</td>";
        } else {
            $Trace .= "<td>" . $Row["class"] . "::" . $Row["function"] . "()</td>";
        }

        if (count($Row["args"]) != 0) {
            $Trace .= "<td><small>" . nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(var_export($Row["args"], TRUE)))) . "</small></td>";
        } else {
            $Trace .= "<td>&nbsp;</td>";
        }

        $Trace .= "</tr>";

    }

    $Trace .= "</table>";

    return $Trace;
}

function pre_var_dump($theVar)

{
    echo "<pre>";
    var_dump($theVar);
    echo "</pre>";
}

function nvl($v1, $v2)

{
    return is_null($v1) ? $v2 : $v1;

}

?>
