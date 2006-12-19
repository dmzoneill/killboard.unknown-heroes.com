<?php
require_once('class.comments.php');

$comments = new Comments($kll_id);
if (isset($_POST['comment']))
{
    $pw = false;
    if (!$config->getConfig('comments_pw') || $page->isAdmin())
    {
        $pw = true;
    }
    if ($_POST['password'] == $config->getPostPassword() || $pw)
    {
        if ($_POST['comment'] == '')
        {
            $html .= "Error: Silent type hey? good for you, bad for a comment.";
        }
        else
        {
            $comment = $_POST['comment'];
            $name = $_POST['name'];
            if ($name == null)
            {
                $name = "Anonymous";
            }
            $comments->addComment($name, $comment);
        }
    }
    else
    {
        // Password is wrong
        $html .= "Error: Wrong Password";
    }
}

$smarty->assign_by_ref('page', $page);

$comment = $comments->getComments();
?>