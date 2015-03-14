<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

/**
 * "Feed" editor.
 */
class FeedEditor implements Editor
{
	/**
	 * Returns content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::serialize()
	 */
	public function serialize($content)
	{
		return json_encode($content);
	}

	/**
	 * Returns serialized content as is.
	 *
	 * @see Icybee\Modules\Editor.Editor::unserialize()
	 */
	public function unserialize($serialized_content)
	{
		return json_decode($serialized_content, true);
	}
	/**
	 * @return FeedEditorElement
	 *
	 * @see Icybee\Modules\Editor.Editor::from()
	 */
	public function from(array $attributes)
	{
		return new FeedEditorElement($attributes);
	}

	// http://tools.ietf.org/html/rfc4287

	public function render($content)
	{
		$app = \ICanBoogie\app();
		$page = $app->request->context->page;
		$site = $page->site;
		$options = $content;

		$constructor = $options['constructor'];
		$limit = $options['limit'];
		$with_author = false;

		if (isset($options['settings']))
		{
			$options['settings']['is_with_author'];
		}

		$gmt_offset = $app->timezone;

		$fdate = $app->locale->calendar->date_formatter;
		$time_pattern = "y-MM-dd'T'HH:mm:ss";

		$host = preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']);
		$page_created_at = $fdate($page->created_at, 'y-MM-dd');

		$entries = $app->models[$constructor]->filter_by_constructor($constructor)->visible->order('date DESC')->limit($limit)->all;

		ob_start();

?>

	<id>tag:<?= $host ?>,<?= $page_created_at ?>:<?= $page->slug ?></id>
	<title><?= $page->title ?></title>
	<link href="<?php echo $page->absolute_url ?>" rel="self" />
	<link href="<?php echo $page->home->absolute_url ?>" />

	<author>
		<name><?php $user = $page->user; echo ($user->firstname && $user->lastname) ? $user->firstname . ' ' . $user->lastname : $user->name ?></name>
	</author>

	<updated><?php

	$updated = '';

	foreach ($entries as $entry)
	{
		if (strcmp($updated, $entry->updated_at) < 0)
		{
			$updated = $entry->updated_at;
		}
	}

	echo $fdate($updated, $time_pattern) . $gmt_offset ?></updated>

<?php

		foreach ($entries as $entry)
		{
?>
	<entry>
		<title><?= \ICanBoogie\escape($entry->title) ?></title>
		<link href="<?php echo $entry->absolute_url ?>" />
		<id>tag:<?= $host ?>,<?php echo $fdate($entry->created_at, 'y-MM-dd') ?>:<?= $entry->slug ?></id>
		<updated><?= $fdate($entry->updated_at, $time_pattern) . $gmt_offset ?></updated>
		<published><?= $fdate($entry->date, $time_pattern) . $gmt_offset ?></published>
		<?php if ($with_author): ?>
		<author>
			<name><?php

			$user = $entry->user;

			echo ($user->firstname && $user->lastname) ? $user->firstname . ' ' . $user->lastname : $entry->user->name ?></name>
		</author>
		<?php endif; ?>
		<?php /*
		<category term="<?php echo $entry->category ?>" /> */ ?>
		<content type="html" xml:lang="<?php echo $entry->language ? $entry->language : $site->language  ?>"><![CDATA[<?php echo $entry ?>]]></content>
	</entry>
<?php
		}

		$rc = ob_get_clean();
		$rc = preg_replace('#(href|src)="/#', '$1="http://' . $host .'/', $rc);

		header('Content-Type: application/atom+xml;charset=utf-8');
		//header('Content-Type: text/plain');

		echo '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">' . $rc . '</feed>';

		exit;
	}
}
