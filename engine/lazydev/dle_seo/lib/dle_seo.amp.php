<?php
/**
 * AMP
 *
 * @link https://lazydev.pro/
 * @author LazyDev <email@lazydev.pro>
 **/
use LazyDev\Seo\Helper;

setlocale(LC_NUMERIC, 'C');

$tpl->load_template('lazydev/dle_seo/amp.tpl');

$fullLink = rtrim($config['http_home_url'], '/') . (explode('/amp.html', $_SERVER['REQUEST_URI'])[0]) . '.html';

if (count($xfields)) {
    $xfieldsdata = xfieldsdataload($row['xfields']);

    foreach ($xfields as $value) {
        $preg_safe_name = preg_quote($value[0], "'");

        if (!isset($xfieldsdata[$value[0]])) {
            $xfieldsdata[$value[0]] = '';
        }

        if ($value[20]) {
            $value[20] = explode(',', $value[20]);

            if ($value[20][0] && !in_array($member_id['user_group'], $value[20])) {
                $xfieldsdata[$value[0]] = '';
            }
        }

        if ($value[3] == 'yesorno') {
            if (intval($xfieldsdata[$value[0]])) {
                $xfgiven = true;
                $xfieldsdata[$value[0]] = $lang['xfield_xyes'];
            } else {
                $xfgiven = false;
                $xfieldsdata[$value[0]] = $lang['xfield_xno'];
            }
        } else {
            $xfgiven = $xfieldsdata[$value[0]] == '' ? false : true;
        }

        if (!$xfgiven) {
            $tpl->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", '', $tpl->copy_template);
            $tpl->copy_template = str_ireplace("[xfnotgiven_{$value[0]}]", '', $tpl->copy_template);
            $tpl->copy_template = str_ireplace("[/xfnotgiven_{$value[0]}]", '', $tpl->copy_template);
        } else {
            $tpl->copy_template = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", '', $tpl->copy_template);
            $tpl->copy_template = str_ireplace("[xfgiven_{$value[0]}]", '', $tpl->copy_template);
            $tpl->copy_template = str_ireplace("[/xfgiven_{$value[0]}]", '', $tpl->copy_template);
        }

        if (strpos($tpl->copy_template, "[ifxfvalue {$value[0]}") !== false) {
            $tpl->copy_template = preg_replace_callback ("#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $tpl->copy_template);
        }

        if (!$value[6] && $value[3] == 'datetime' && !empty($xfieldsdata[$value[0]])) {
            $xfieldsdata[$value[0]] = strtotime(str_replace('&#58;', ':', $xfieldsdata[$value[0]]));
            if (!trim($value[24])) {
                $value[24] = $config['timestamp_active'];
            }

            if ($value[25]) {
                $xfieldsdata[$value[0]] = $value[26] ? langdate($value[24], $xfieldsdata[$value[0]]) : langdate($value[24], $xfieldsdata[$value[0]], false, $customlangdate);
            } else {
                $xfieldsdata[$value[0]] = date($value[24], $xfieldsdata[$value[0]]);
            }
        }

        if ($value[3] == 'image' && $xfieldsdata[$value[0]]) {
            $temp_array = explode('|', $xfieldsdata[$value[0]]);

            if ($config['version_id'] >= 15.0) {
                if (count($temp_array) == 1 || count($temp_array) == 5) {
                    $temp_alt = '';
                    $temp_value = implode('|', $temp_array);
                } else {
                    $temp_alt = $temp_array[0];
                    $temp_alt = str_replace("&amp;#44;", "&#44;", $temp_alt);
                    $temp_alt = str_replace("&amp;#124;", "&#124;", $temp_alt);

                    unset($temp_array[0]);
                    $temp_value = implode('|', $temp_array);
                }

                $path_parts = get_uploaded_image_info($temp_value);
                $img_url = $path_parts->url;
            } else {
                $temp_alt = '';
                $temp_value = $temp_array[0];
                if (count($temp_array) > 1) {
                    $temp_alt = $temp_array[0];
                    $temp_value = $temp_array[1];
                }

                $path_parts = @pathinfo($temp_value);
                $img_url = $config['http_home_url'] . 'uploads/posts/' . $path_parts['dirname'] . '/' . $path_parts['basename'];
            }
            $xfieldsdata[$value[0]] = "<amp-img layout=\"responsive\" src=\"{$img_url}\" alt=\"{$temp_alt}\"></amp-img>";
            $tpl->set("[xfvalue_image_url_{$value[0]}]", $img_url);
        }

        if ($value[3] == 'image' && !$xfieldsdata[$value[0]]) {
            $tpl->set("[xfvalue_image_url_{$value[0]}]", '');
        }

        if ($value[3] == 'imagegalery' && $xfieldsdata[$value[0]] && stripos($tpl->copy_template, "[xfvalue_{$value[0]}") !== false) {
            $fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);
            $gallery_image = [];
            $gallery_single_image = [];
            $xf_image_count = 0;
            $single_need = false;
            $single_url = false;
            if (stripos($tpl->copy_template, "[xfvalue_{$value[0]} image=") !== false) {
                $single_need = true;
            }

            if (stripos($tpl->copy_template, "[xfvalue_{$value[0]} url=") !== false) {
                $single_url = true;
            }

            foreach ($fieldvalue_arr as $temp_value) {
                $xf_image_count++;
                $temp_value = trim($temp_value);

                if ($temp_value == '') {
                    continue;
                }

                $temp_array = explode('|', $temp_value);

                if ($config['version_id'] >= 15.0) {
                    if (count($temp_array) == 1 || count($temp_array) == 5) {
                        $temp_alt = '';
                        $temp_value = implode('|', $temp_array);
                    } else {
                        $temp_alt = $temp_array[0];
                        $temp_alt = str_replace("&amp;#44;", "&#44;", $temp_alt);
                        $temp_alt = str_replace("&amp;#124;", "&#124;", $temp_alt);

                        unset($temp_array[0]);
                        $temp_value = implode('|', $temp_array);
                    }

                    $path_parts = get_uploaded_image_info($temp_value);
                    $img_url = $path_parts->url;
                } else {
                    $temp_alt = '';
                    $temp_value = $temp_array[0];
                    if (count($temp_array) > 1) {
                        $temp_alt = $temp_array[0];
                        $temp_value = $temp_array[1];
                    }

                    $path_parts = @pathinfo($temp_value);
                    $img_url = $config['http_home_url'] . 'uploads/posts/' . $path_parts['dirname'] . '/' . $path_parts['basename'];
                }
                $gallery_image[] = "<li><amp-img layout=\"responsive\" src=\"{$img_url}\" alt=\"{$temp_alt}\"></amp-img></li>";
                $gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<amp-img layout=\"responsive\" src=\"{$img_url}\" alt=\"{$temp_alt}\"></amp-img>";
                $gallery_single_image_url['[xfvalue_'.$value[0].' url="'.$xf_image_count.'"]'] = $img_url;
            }

            if ($single_need && count($gallery_single_image)) {
                foreach ($gallery_single_image as $temp_key => $temp_value) {
                    $tpl->set($temp_key, $temp_value);
                }
            }

            if ($single_url && count($gallery_single_image_url)) {
                foreach ($gallery_single_image_url as $temp_key => $temp_value) {
                    $tpl->set($temp_key, $temp_value);
                }
            }

            $xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">" . implode($gallery_image) . "</ul>";
        }

        $tpl->set("[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]]);

        if (preg_match("#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template, $matches)) {
            $tpl->set($matches[0], Helper::cutString($xfieldsdata[$value[0]], intval($matches[1])));
        }
    }
}

$author = $config['version_id'] > 13.1 ? rawurlencode($row['autor']) : urlencode($row['autor']);
$authorPage = $config['allow_alt_url'] ? $config['http_home_url'] . 'user/' . $author . '/' : $PHP_SELF . '?subaction=userinfo&amp;user='. $author;

$news_date = strtotime($row['date']);
$tpl->copy_template = preg_replace_callback("#\{date=(.+?)\}#i", 'formdate', $tpl->copy_template);

$tpl->set('', [
    '{login}' => stripslashes($row['autor']),
    '{profile}' => $authorPage,
    '[profile]' => "<a href=\"{$authorPage}\">",
    '[/profile]' => '</a>',
    '{views}' => number_format($row['news_read'], 0, ',', ' '),
    '{date}' => date('d.m.Y', strtotime($row['date'])),
    '{shema-date}' => str_replace(' ', 'T', $row['date']),
    '{seo-date}' => str_replace(' ', 'T', $row['date']),
    '{title}' => stripslashes($row['title']),
    '{shema-title}' => str_replace("&amp;amp;", "&amp;", htmlspecialchars(stripslashes($row['title']), ENT_QUOTES, $config['charset'])),
    '{json-title}' => str_replace("&amp;amp;", "&amp;", htmlspecialchars(stripslashes($row['title']), ENT_QUOTES, $config['charset'])),
    '{site-url}' => $config['http_home_url'],
    '{site-name}' => $config['home_title'],
    '{site-short}' => $config['short_title'],
    '[full-link]' => "<a href=\"{$fullLink}\">",
    '[/full-link]' => '</a>',
    '{full-link}' => $fullLink,
    '{category}' => $my_cat,
    '{link-category}' => $my_cat_link
]);

$content = !empty($row['full_story']) ? stripslashes($row['full_story']) : stripslashes($row['short_story']);
$content = preg_replace([
    "'{banner_(.*?)}'si",
    "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si",
    "'\[page=(.*?)\](.*?)\[/page\]'si"
], '', $content);
$cutContent = preg_replace([
    "#<!--TBegin(.+?)<!--TEnd-->#is",
    "#<!--MBegin(.+?)<!--MEnd-->#is",
    "#<!--dle_spoiler(.+?)<!--spoiler_text-->#is",
    "#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is",
    "'\[attachment=(.*?)\]'si",
    "#\[hide(.*?)\](.+?)\[/hide\]#is"
], '', $content);
$cutContent = str_replace('{PAGEBREAK}', '', $cutContent);
$tpl->set('{description}', str_replace("&amp;amp;", "&amp;", htmlspecialchars(strip_tags(Helper::cutString($cutContent, 160)), ENT_QUOTES, $config['charset'])));
$tpl->set('{full-story}', $content);

$row['short_story'] = stripslashes($row['short_story']);
$row['full_story'] = stripslashes($row['full_story']);
$row['xfields'] = stripslashes($row['xfields']);

$images = [];
preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'] . $row['full_story'] . $row['xfields'], $media);
$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

foreach ($data as $url) {
    $info = pathinfo($url);
    if (isset($info['extension'])) {
        if ($info['filename'] == 'spoiler-plus' || $info['filename'] == 'spoiler-minus' || strpos($info['dirname'], 'engine/data/emoticons') !== false) {
            continue;
        }

        $info['extension'] = strtolower($info['extension']);
        if (in_array($info['extension'],  ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            $images[] = $url;
        }
    }
}

if (count($images)) {
    $i = 0;
    foreach ($images as $url) {
        $i++;
        $tpl->copy_template = str_replace('{image-'.$i.'}', $url, $tpl->copy_template);
        $tpl->copy_template = str_replace('[image-'.$i.']', '', $tpl->copy_template);
        $tpl->copy_template = str_replace('[/image-'.$i.']', '', $tpl->copy_template);
        $tpl->copy_template = preg_replace("#\[not-image-{$i}\](.+?)\[/not-image-{$i}\]#is", '', $tpl->copy_template);
    }
}

$tpl->copy_template = preg_replace("#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", '', $tpl->copy_template);
$tpl->copy_template = preg_replace("#\\{image-(.+?)\\}#i", '{THEME}/dleimages/no_image.jpg', $tpl->copy_template);
$tpl->copy_template = preg_replace("#\[not-image-(.+?)\]#i", '', $tpl->copy_template);
$tpl->copy_template = preg_replace("#\[/not-image-(.+?)\]#i", '', $tpl->copy_template);

$tpl->compile('amp');
$tpl->clear();

$tpl->result['amp'] = preg_replace(
    '/<img src="([^"]*)"\s*\/?>/',
    '<amp-img src="$1" layout="responsive"></amp-img>',
    $tpl->result['amp']
);
$tpl->result['amp'] = str_ireplace(
    ['<iframe', '<video', '</iframe', '</video', '<audio', '</audio', '<form', '</form'],
    ['<amp-iframe', '<amp-video', '</amp-iframe', '</amp-video', '<amp-audio', '</amp-audio', '<amp-form', '</amp-form'],
    $tpl->result['amp']
);

$tpl->result['amp'] = str_replace('{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['amp']);
echo $tpl->result['amp'];
die();