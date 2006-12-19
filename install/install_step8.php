<?php
$stoppage = true;

@unlink('../cache/config.php');
?>
<div class="block-header2">Installation Complete</div>
<p>Congratulations, you successfully installed the EVE Development Network Killboard v1.2!<br/>
Please check <a href="http://www.eve-dev.net/">EVE-Dev.net</a> for updates from time to time.<br/>
<br/>
You can now take a look at <a href="../">your new Killboard</a>.<br/><br/>
<b>Don't forget to delete the install folder now or restrict the access to it!</b><br/>
</p>
<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>