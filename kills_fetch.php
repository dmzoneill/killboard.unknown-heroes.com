<?php

/**
 * Shared killmail scraper used by kills.php and kills2.php.
 *
 * Fetches kill mails from the Freelancer Alliance killboard and stores
 * Unknown-Heroes kills under UNKH and all others under FLA.
 */

/**
 * Save one killmail body to the given directory.
 *
 * @param string $dir     Target directory name (UNKH or FLA).
 * @param int    $id      Kill id.
 * @param string $mail    Killmail text.
 * @param string $success Message printed on success.
 *
 * @return void
 */
function save_killmail($dir, $id, $mail, $success)
{
    $mail = str_ireplace("&nbsp;", "", trim($mail));
    $handle = fopen("C:\\xampp\\htdocs\\$dir\\$id.txt", 'w');
    $written = $handle === false ? false : fputs($handle, $mail);
    if ($written !== false && $written > 0) {
        print "$success $id \n";
    } else {
        print "Error $id\n";
    }
}

/**
 * Fetch killmails for ids in [$start, $end).
 *
 * @param int  $start         First kill id.
 * @param int  $end           Kill id to stop before.
 * @param bool $stopOnMissing Abort when a page cannot be requested.
 *
 * @return void
 */
function fetch_killmails($start, $end, $stopOnMissing)
{
    for ($i = $start; $i < $end; $i++) {
        $url = "http://freelancer-alliance.net/Killboard/index.php?a=kill_mail&kll_id=$i";
        $fp = @fopen("$url", "r");
        if ($fp) {
            $current_line = stream_get_contents($fp);
            if ($current_line === false) {
                $current_line = "";
            }
            $mail = explode("Original Killmail", (str_ireplace("<br />", "", strip_tags($current_line))));
            $mail = str_ireplace("<br />", "", $mail[1]);

            if (stristr($mail, "Corp: Unknown-Heroes") !== false) {
                save_killmail("UNKH", $i, $mail, "Success retrieved UNKH Killmail");
            } else {
                save_killmail("FLA", $i, $mail, "Success retrieved non UNKH Killmail");
            }

            fclose($fp);
        } elseif ($stopOnMissing) {
            die("Unable to request page on id : $i \n");
        } else {
            print "Kill id: $i does not exist\n";
        }
    }
}
