<?php
require_once("config.php");
require_once("class.killboard.php");
require_once("class.session.php");

class Page
{
    function Page($title = "", $cachable = true)
    {
        $this->title_ = $title;
        $this->admin_ = false;

        if (substr($_SERVER['HTTP_USER_AGENT'], 0, 15) == "EVE-minibrowser")
        {
            $this->igb_ = true;
        }
        else
        {
            $this->igb_ = false;
        }

        $this->timestart_ = strtok(microtime(), ' ') + strtok('');

        $this->killboard_ = new Killboard(KB_SITE);

        $this->session_ = new Session();

        $this->cachable_ = $cachable;
        $this->cachetime_ = 5;
    }

    function setContent($html)
    {
        $this->contenthtml_ = $html;
    }

    function addContext($html)
    {
        $this->contexthtml_ .= $html;
    }

    function generate()
    {
        global $config, $smarty;

        $smarty->assign('kb_title', KB_TITLE.' Killboard - '.$this->title_);

        $style = $config->getStyleName();
        $smarty->assign('style', $style);

        $smarty->assign('common_url', COMMON_URL);
        if ($this->onload_)
        {
            $smarty->assign('on_load', ' onload="'.$this->onload_.'"');
        }
        // header

        if (!$this->igb_)
        {
            if (MAIN_SITE)
            {
                $smarty->assign('banner_link', MAIN_SITE);
            }
            $banner = $config->getStyleBanner();
            if ($banner == 'custom')
            {
                $banner = 'kb-banner.jpg';
            }
            $smarty->assign('banner', $banner);

            $menu = new Menu();
            $menu->add('home', 'Home');

            $contracts = $this->killboard_->hasContracts();
            $campaigns = $this->killboard_->hasCampaigns();
            if ($contracts)
            {
                $menu->add('contracts', 'Contracts');
            }
            if ($campaigns)
            {
                $menu->add('campaigns', 'Campaigns');
            }
            $w = 10;
            if ($campaigns)
            {
                $w--;
            }
            if ($contracts)
            {
                $w--;
            }
            if ($config->getConfig('show_standing'))
            {
                $w--;
                $menu->add('standings', 'Standings');
            }
            $menu->add('kills', 'Kills');
            $menu->add('losses', 'Losses');
            $menu->add('post', 'Post Mail');

            if (CORP_ID)
            {
                $link = 'corp_detail&crp_id='.CORP_ID;
            }
            elseif (ALLIANCE_ID)
            {
                $link = 'alliance_detail&all_id='.ALLIANCE_ID;
            }
            $menu->add($link, 'Stats');
            $menu->add('awards', 'Awards');
            $menu->add('search', 'Search');
            $menu->add('admin', 'Admin');
            $menu->add('about', 'About');

            $smarty->assign('menu_w', $w.'%');
            $smarty->assign('menu', $menu->get());
        }
        $smarty->assign('page_title', $this->title_);

        $this->timeend_ = strtok(microtime(), ' ') + strtok('');
        $this->processingtime_ = $this->timeend_ - $this->timestart_;

        $smarty->assign('profile_time', $this->processingtime_);
        $smarty->assign('profile', KB_PROFILE);
        $smarty->assign('content_html', $this->contenthtml_);
        $smarty->assign('context_html', $this->contexthtml_);
        $smarty->display(get_tpl('index'));
    }

    function igb()
    {
        return $this->igb_;
    }

    function setOnLoad($onload)
    {
        $this->onload_ = $onload;
    }

    function setTitle($title)
    {
        $this->title_ = $title;
    }

    function setAdmin()
    {
        if (!$this->session_->isAdmin())
        {
            header("Location: ?a=login");
            echo '<a href="?a=login">Login</a>';
            exit;
        }
    }

    function isAdmin()
    {
        return $this->session_->isAdmin();
    }

    function isSuperAdmin()
    {
        return $this->session_->isSuperAdmin();
    }

    function setSuperAdmin()
    {
        if (!$this->session_->isSuperAdmin())
            Header("Location: ?a=login");
    }

    function setCachable($cachable)
    {
        $this->cachable_ = $cachable;
    }

    function setCacheTime($cachetime)
    {
        $this->cachetime_ = $cachetime;
    }

    function error($errormsg)
    {
        echo $errormsg;
        exit;
    }
}

class Menu
{
    function Menu()
    {
        $this->menu_ = array();
    }

    function get()
    {
        return $this->menu_;
    }

    function add($link, $text)
    {
        $this->menu_[] = array('link' => $link, 'text' => $text);
    }
}
?>