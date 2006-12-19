<?php
require_once("db.php");
require_once("class.page.php");
require_once("globals.php");

$page = new Page("Search");

$html .= "<form id=search action=\"?a=search\" method=post>";
$html .= "<table class=kb-subtable><tr>";
$html .= "<td>Type:</td><td>Text: (3 letters minimum)</td>";
$html .= "</tr><tr>";
$html .= "<td><select id=searchtype name=searchtype><option value=pilot>Pilot</option><option value=corp>Corporation</option><option value=alliance>Alliance</option></select></td>";
$html .= "<td><input id=searchphrase name=searchphrase type=text size=30/></td>";
$html .= "<td><input type=submit name=submit value=Search></td>";
$html .= "</tr></table>";
$html .= "</form>";

if ($_REQUEST['searchphrase'] != "" && strlen($_REQUEST['searchphrase']) >= 3)
{
    switch ($_REQUEST['searchtype'])
    {
        case "pilot":
            $sql = "select plt.plt_id, plt.plt_name, crp.crp_name
                  from kb3_pilots plt, kb3_corps crp
                 where lower( plt.plt_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                   and plt.plt_crp_id = crp.crp_id
                 order by plt.plt_name";
            $header = "<td>Pilot</td><td>Corporation</td>";
            break;
        case "corp":
            $sql = "select crp.crp_id, crp.crp_name, ali.all_name
                  from kb3_corps crp, kb3_alliances ali
                 where lower( crp.crp_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                   and crp.crp_all_id = ali.all_id
                 order by crp.crp_name";
            $header = "<td>Corporation</td><td>Alliance</td>";
            break;
        case "alliance":
            $sql = "select ali.all_id, ali.all_name
                  from kb3_alliances ali
                 where lower( ali.all_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                 order by ali.all_name";
            $header = "<td>Alliance</td><td></td>";
            break;
    }

    $qry = new DBQuery();
    if (!$qry->execute($sql))
    {
        die ($qry->getErrorMsg());
    }

    $html .= "<div class=block-header>Search results</div>";

    if ($qry->recordCount() > 0)
    {
        $html .= "<table class=kb-table width=450 cellspacing=1>";
        $html .= "<tr class=kb-table-header>".$header."</tr>";
    }
    else
    {
        $html .= "No results.";
    }

    while ($row = $qry->getRow())
    {
        $html .= "<tr class=kb-table-row-even>";
        switch ($_REQUEST['searchtype'])
        {
            case "pilot":
                $link = "?a=pilot_detail&plt_id=".$row['plt_id'];
                $html .= "<td><a href=\"$link\">".$row['plt_name']."</a></td><td>".$row['crp_name']."</td>";
                break;
            case "corp":
                $link = "?a=corp_detail&crp_id=".$row['crp_id'];
                $html .= "<td><a href=\"$link\">".$row['crp_name']."</a></td><td>".$row['all_name']."</td>";
                break;
            case "alliance":
                $link = "?a=alliance_detail&all_id=".$row['all_id'];
                $html .= "<td><a href=\"$link\">".$row['all_name']."</a></td><td></td>";
                break;
        }
        $html .= "</tr>";
        if ($qry->recordCount() == 1)
        {
            // if there is only one entry we redirect the user directly
            header("Location: $link");
        }
    }
    if ($qry->recordCount() > 0)
    {
        $html .= "</table>";
    }
}

$page->setContent($html);
$page->generate();
?>