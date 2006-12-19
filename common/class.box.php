<?php
require_once("class.graph.php");
require_once("globals.php");

class Box
{
    function Box($title = "")
    {
        $this->title_ = $title;
		$this->box_array = array();
    }

    function setIcon($icon)					//its called setIcon... and it sets the Icon.
    {
        $this->icon_ = $icon;
    }

	function addOption($type,$name,$url="")	//add something to the array that we send to smarty later... types can be caption, img, link, points. Only link needs all 3 attribues
	{
	$this->box_array[]=array('type' => $type, 'name' => $name, 'url' => $url);
	}

	function generate()
    {
	//print_r($this->box_array);
	 global $config, $smarty;
	$smarty->assign('count',count($this->box_array));
	if($this->icon_){ $smarty->assign('icon',IMG_URL . "/" . $this->icon_); }
	$smarty->assign('title',$this->title_ );
	$smarty->assign('items',$this->box_array);
	return $smarty->fetch(get_tpl('box'));
    }
}

class AwardBox
{
    function AwardBox($list, $title, $comment, $entity, $award)
    {

        $this->toplist_ = $list;
        $this->title_ = $title;
        $this->comment_ = $comment;
        $this->entity_ = $entity;
        $this->award_ = $award;
    }

    function generate()
    {
		global $config, $smarty;
        $rows = array();
        $max = 0;

        for ($i = 1; $i < 11; $i++)
        {
            $row = $this->toplist_->getRow();
            if ($row) array_push($rows, $row);
            if ($row['cnt'] > $max) $max = $row['cnt'];
        }

        if (!$rows[0]['plt_id']) return;
        $pilot = new Pilot($rows[0]['plt_id']);
		$smarty->assign('title',$this->title_);
		$smarty->assign('pilot_portrait',$pilot->getPortraitURL(64));
		$smarty->assign('award_img',IMG_URL . "/awards/" . $this->award_ . ".gif");
		$smarty->assign('url',"?a=pilot_detail&plt_id=" . $rows[0]['plt_id'] );
		$smarty->assign('name',$pilot->getName() );
        $bar = new BarGraph($rows[0]['cnt'], $max, 60);
		$smarty->assign('bar',$bar->generate());
		$smarty->assign('cnt', $rows[0]['cnt']);

        for ($i = 2; $i < 11; $i++)
        {
            if (!$rows[$i - 1]['plt_id']) break;
            $pilot = new Pilot($rows[$i - 1]['plt_id']);
			$bar = new BarGraph($rows[$i - 1]['cnt'], $max, 60);
			$top[$i] = array('url'=> "?a=pilot_detail&plt_id=" . $rows[$i - 1]['plt_id'], 'name'=>$pilot->getName(),'bar'=>$bar->generate(),'cnt'=>$rows[$i - 1]['cnt']);
        }
		$smarty->assign('top',$top);
		$smarty->assign('comment',$this->comment_);
		return $smarty->fetch(get_tpl('award_box'));
    }

}
?>
