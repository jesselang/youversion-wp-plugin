<?php
/**
 * @package YouVersion
 * @author Dan Frist
 * @version 2
 */
/*
Plugin Name: YouVersion
Plugin URI: http://www.youversion.com/
Description: This plugin is used to link scripture references to YouVersion.com
Author: Dan Frist
Version: 2
Author URI: http://twitter.com/danfrist
Text Domain: youversion
*/

function yv_generate_links( $text ) {

	// if there is a youversion tag in the text
	if( strpos( $text, '[youversion]' ) !== false ) {

		// exlode the text into an array
		$text = explode( '[youversion]', $text );

		// loop through array
		foreach( $text as $row ) {

			// if this row has a
			if( strpos( $row, '[/youversion]' ) != false ) {

				// explode this return in case there is more text after the tag
				$row_exploded = explode( '[/youversion]', $row );

				// trim away closing tag
				$row_exploded[0] = preg_replace( '/\[\/youversion\].*/', '', $row_exploded[0] );

				// list of books and their abbreviations  (OSIS)
				$osis = array('Genesis' => 'Gen', 'Exodus' => 'Exod', 'Leviticus' => 'Lev', 'Numbers' => 'Num', 'Deuteronomy' => 'Deut', 'Joshua' => 'Josh', 'Judges' => 'Judg', 'Ruth' => 'Ruth', '1 Samuel' => '1Sam', '2 Samuel' => '2Sam', '1 Kings' => '1Kgs', '2 Kings' => '2Kgs', '1 Chronicles' => '1Chr', '2 Chronicles' => '2Chr', 'Ezra' => 'Ezra', 'Nehemiah' => 'Neh', 'Esther' => 'Esth', 'Job' => 'Job', 'Psalms' => 'Ps', 'Proverbs' => 'Prov', 'Ecclesiastes' => 'Eccl', 'Song of Solomon' => 'Song', 'Isaiah' => 'Isa', 'Jeremiah' => 'Jer', 'Lamentations' => 'Lam', 'Ezekiel'=>'Ezek', 'Daniel' => 'Dan', 'Hosea' => 'Hos', 'Joel' => 'Joel', 'Amos' => 'Amos', 'Obadiah' => 'Obad', 'Jonah' => 'Jonah', 'Micah' => 'Mic', 'Nahum' => 'Nah', 'Habakkuk' => 'Hab', 'Zephaniah' => 'Zeph', 'Haggai' => 'Hag', 'Zechariah' => 'Zech', 'Malachi' => 'Mal', 'Matthew' => 'Matt', 'Mark' => 'Mark', 'Luke' => 'Luke', 'John' => 'John', 'Acts' => 'Acts', 'Romans' => 'Rom', '1 Corinthians' => '1Cor', '2 Corinthians' => '2Cor', 'Galatians' => 'Gal', 'Ephesians' => 'Eph', 'Philippians' => 'Phil', 'Colossians' => 'Col', '1 Thessalonians' =>'1Thess', '2 Thessalonians' => '2Thess', '1 Timothy' => '1Tim', '2 Timothy' => '2Tim', 'Titus' => 'Titus', 'Philemon' => 'Phlm', 'Hebrews' => 'Heb', 'James' => 'Jas', '1 Peter' => '1Pet', '2 Peter' => '2Pet', '1 John' => '1John', '2 John' => '2John', '3 John' => '3John', 'Jude'=> 'Jude', 'Revelation' => 'Rev');


				// change book name to abbreviated book name
				foreach( $osis as $key=>$value ) {
					if( stristr( $row_exploded[0], $key ) != false ) {
						$reference_link = str_replace( $key, $value . '.', $row_exploded[0] );
						break;
					}
				}

				// change : to /
				$reference_link = str_replace( ':', '.', $reference_link );

				// get version if specified
				$last_dot = strrpos( $reference_link, '.');
                                $last_space = strrpos( $reference_link, ' ', $last_dot + 1);				

				if( $last_space === false ) {
					$version = get_option( 'yv_bible_version' );
				} else {
					$version_length = strlen($reference_link) - $last_space - 1;
					if( $version_length >= 3 && $version_length <= 6 ) {
						$version = strtolower(substr( $reference_link, $last_space + 1 ));
						$reference_link = substr( $reference_link, 0, $last_space);
					} else {
						$version = get_option( 'yv_bible_version');
					}
				}

				// remove any spaces
				$reference_link = str_replace( ' ', '', $reference_link );

				// put the text in the tag in a link
				$row_exploded[0] = '<a target="_blank" href="http://www.youversion.com/bible/' . $version . '/' . $reference_link . '">' . $row_exploded[0] . '</a>';

				// put the link and any text after it back together
				$row = implode( $row_exploded );

			}

			$output[] = $row;

		}

	}
	else {

		$output = $text;

	}

	// if this is an array (if text had a youversion tag in it) put it back into a sting, else output string
	return ( is_array( $output ) ) ? implode( $output ) : $output;

}

function yv_config() {

	// set lang domain
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'youversion', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

	// if settings have been posted
	if( isset( $_POST['version'] ) ) {

		// if the option already exists, update it, else add it
		( get_option( 'yv_bible_version' ) ) ? update_option( 'yv_bible_version', $_POST['version'] ) : add_option( 'yv_bible_version', $_POST['version'] ) ;

	}

	// get current version of bible from db for selecting list item
	$current_bible_version = get_option( 'yv_bible_version' );

	?>
	<h1><?php _e( "YouVersion Plugin" ) ?></h1>

	<table>

		<tr>

			<td valign="top">

				<p><strong><?php _e( "Settings" ) ?></strong></p>

				<p>

					<form action="" method="post">

						<dl>
							<dt><label for="bible_version"><?php _e( "Bible Version:" ) ?></label></dt>
							<dd>
								<select id="version" name="version">
									<option value="bg1940" <?php if( $current_bible_version == 'bg1940' ) echo 'selected="selected"'; ?> >Bulgarian 1940</option>
									<option value="csbkr" <?php if( $current_bible_version == 'csbkr' ) echo 'selected="selected"'; ?> >Czech Bible Kralicka 1613</option>
									<option value="elb" <?php if( $current_bible_version == 'elb' ) echo 'selected="selected"'; ?> >Elberfelder Bibel</option>
									<option value="delut" <?php if( $current_bible_version == 'delut' ) echo 'selected="selected"'; ?> >Luther Bible 1545</option>
									<option value="asv" <?php if( $current_bible_version == 'asv' || !$current_bible_version ) echo 'selected="selected"'; ?> >American Standard Version</option>
									<option value="amp" <?php if( $current_bible_version == 'amp' ) echo 'selected="selected"'; ?> >Amplified Bible</option>
									<option value="cev" <?php if( $current_bible_version == 'cev' ) echo 'selected="selected"'; ?> >Contemporary English Version</option>
									<option value="esv" <?php if( $current_bible_version == 'esv' ) echo 'selected="selected"'; ?> >English Standard Version</option>
									<option value="gwt" <?php if( $current_bible_version == 'gwt' ) echo 'selected="selected"'; ?> >GOD'S WORD Translation</option>
									<option value="hcsb" <?php if( $current_bible_version == 'hcsb' ) echo 'selected="selected"'; ?> >Holman Christian Standard Bible</option>
									<option value="kjv" <?php if( $current_bible_version == 'kjv' ) echo 'selected="selected"'; ?> >King James Version</option>
									<option value="net" <?php if( $current_bible_version == 'net' ) echo 'selected="selected"'; ?> >New English Translation</option>
									<option value="nasb" <?php if( $current_bible_version == 'nasb' ) echo 'selected="selected"'; ?> >New American Standard Bible</option>
									<option value="ncv" <?php if( $current_bible_version == 'ncv' ) echo 'selected="selected"'; ?> >New Century Version</option>
									<option value="niv" <?php if( $current_bible_version == 'niv' ) echo 'selected="selected"'; ?> >New International Version</option>
									<option value="nkjv" <?php if( $current_bible_version == 'nkjv' ) echo 'selected="selected"'; ?> >New King James Version</option>
									<option value="tniv" <?php if( $current_bible_version == 'tniv' ) echo 'selected="selected"'; ?> >Today's New International Version</option>
									<option value="nlt" <?php if( $current_bible_version == 'nlt' ) echo 'selected="selected"'; ?> >New Living Translation</option>
									<option value="msg" <?php if( $current_bible_version == 'msg' ) echo 'selected="selected"'; ?> >The Message</option>
									<option value="web" <?php if( $current_bible_version == 'web' ) echo 'selected="selected"'; ?> >World English Bible</option>
									<option value="lbla" <?php if( $current_bible_version == 'lbla' ) echo 'selected="selected"'; ?> >La Biblia de las Americas</option>
									<option value="nblh" <?php if( $current_bible_version == 'nblh' ) echo 'selected="selected"'; ?> >Nueva Biblia de los Hispanos</option>
									<option value="nvi" <?php if( $current_bible_version == 'nvi' ) echo 'selected="selected"'; ?> >Nueva Version Internacional</option>
									<option value="rves" <?php if( $current_bible_version == 'rves' ) echo 'selected="selected"'; ?> >Reina-Valera Antigua</option>
									<option value="finpr" <?php if( $current_bible_version == 'finpr' ) echo 'selected="selected"'; ?> >Finnish 1938</option>
									<option value="lsg" <?php if( $current_bible_version == 'lsg' ) echo 'selected="selected"'; ?> >Louis Segond</option>
									<option value="idbar" <?php if( $current_bible_version == 'idbar' ) echo 'selected="selected"'; ?> >Terjemahan Baru</option>
									<option value="itriv" <?php if( $current_bible_version == 'itriv' ) echo 'selected="selected"'; ?> >Italian Riveduta (1927)</option>
									<option value="ja1955" <?php if( $current_bible_version == 'ja1955' ) echo 'selected="selected"'; ?> >Colloquial Japanese (1955)</option>
									<option value="sv1750" <?php if( $current_bible_version == 'sv1750' ) echo 'selected="selected"'; ?> >Statenvertaling</option>
									<option value="norsk" <?php if( $current_bible_version == 'norsk' ) echo 'selected="selected"'; ?> >Det Norsk Bibelselskap 1930</option>
									<option value="aa" <?php if( $current_bible_version == 'aa' ) echo 'selected="selected"'; ?> >Almeida Atualizada</option>
									<option value="rmnn" <?php if( $current_bible_version == 'rmnn' ) echo 'selected="selected"'; ?> >Romanian Cornilescu 1928</option>
									<option value="sven" <?php if( $current_bible_version == 'sven' ) echo 'selected="selected"'; ?> >Svenska 1917</option>
									<option value="vi1934" <?php if( $current_bible_version == 'vi1934' ) echo 'selected="selected"'; ?> >1934 Vietnamese Bible</option>
								</select>
							</dd>
						</dl>
						<input type="submit" value="Save Settings" >
					</form>

				</p>

				<hr />

				<p><strong><?php _e( "Instructions on How To Use the [youversion] Tags" ) ?></strong></p>

				<p><?php _e( "The YouVersion Wordpress plugin gives you the ability to quickly link to Bible verses using a simple tag structure that's familiar to Wordpress." ) ?></p>
				
				<p><?php _e( "First, make sure to choose the Bible version you want all links to use. You can change this setting using the drop down list above." ) ?></p>
				
				<p><?php echo sprintf( __( "Second, when you create a new post or page on your Wordpress powered website, use this format %sPLAIN TEXT REFERENCE%s to create a reference with a link to that verse on YouVersion." ), '<code>[youversion]', '[/youversion]</code>' ) ?></p>

				<p><strong><?php _e( "Example:" ) ?></strong></p>

				<ul>
					<li><?php echo sprintf( __( 'In the text editor, type: "%sHi, my name is Scott and %s is my favorite verse.%s"' ), '<em>', '[youversion]John 3:16[/youversion]', '</em>' ) ?></li>
					<li><?php echo sprintf( __( 'When you publish the post or page, it will look like: "%sHi, my name is Scott and %s is my favorite verse.%s"' ), '<em>', '<a href="http://www.youversion.com/bible/asv/john/3/16">John 3:16</a>', '</em>' ) ?></li>
				</ul>

				<p><?php _e( 'Remember to spell the verse reference properly and use the commonly accepted format for Bible references (ie. John 3:16). The reference formats that work are "John 3:16" and "John 3:16-18".' ) ?></p>

				<p><?php _e( "References that use commas (ie. John 3:16,18) or multi-chapter spans (ie. John 3:16-4:5) will not work and will result in a link that leads to a dead page on YouVersion.com." ) ?></p>

				<hr />

				<p><strong><?php _e( "Information About YouVersion" ) ?></strong></p>

				<p><?php _e( "YouVersion is an online Bible tool that offers 41 Bible versions in over 20 languages. At YouVersion.com, you can read the Bible in an innovative format, share your Bible reading experience with your friends, create Contributions with rich media and Journal entries that are tied to passages of Scripture, or subscribe to one of our 22 Bible reading plans." ) ?></p>

				<p><?php _e( "YouVersion.com has given you the ability to engage with Scripture like never before, and with YouVersion mobile you have access to the Bible, corresponding contributions, reading plans, and online community no matter where you are. Our YouVersion mobile apps put the YouVersion experience in the palm of your hand. Apps are available for the iPhone, iPod Touch, Blackberry, Android, Palm's WebOS, Java, and the mobile web." ) ?></p>

				<p><?php echo sprintf( __( "Learn more about our mobile Bible applications at %s." ), '<a href="http://youversion.com/mobile">http://youversion.com/mobile</a>' ) ?></p>

				<hr />

				<p><strong><?php _e( "How to Grab the YouVersion Social Badge" ) ?></strong></p>

				<p><?php _e( "Along with the YouVersion Wordpress plugin, we've created a simple-but-attractive badge (the YouVersion Social Badge) that you can embed in the sidebar of your blog or website. It can display your YouVersion avatar, username, the date you joined YouVersion, the number of followers you have on YouVersion, and your three most recent public Contributions." ) ?></p>

				<p><?php echo sprintf( __( "The YouVersion Social Badge is a great way to show your website visitors that you're an active member of the YouVersion community and easily share your Contributions in your sidebar. Go to %s to grab the YouVersion Social Badge." ), '<a href="http://youversion.com/badges">http://youversion.com/badges</a>' ) ?></p>

			</td>
			<td width="10%" style="padding:0 20px; white-space:nowrap">

				<p><strong><?php _e( "Acceptable book names:" ) ?></strong></p>

				<table>
					<tr>
						<td valign="top" width="50%" style="padding-right:10px">

							<p>
								<i>Old Testament</i>
								<hr/>
								Genesis<br />
								Exodus<br />
								Leviticus<br />
								Numbers<br />
								Deuteronomy<br />
								Joshua<br />
								Judges<br />
								Ruth<br />
								1 Samuel<br />
								2 Samuel<br />
								1 Kings<br />
								2 Kings<br />
								1 Chronicles<br />
								2 Chronicles<br />
								Ezra<br />
								Nehemiah<br />
								Esther<br />
								Job<br />
								Psalms<br />
								Proverbs<br />
								Ecclesiastes<br />
								Song of Solomon<br />
								Isaiah<br />
								Jeremiah<br />
								Lamentations<br />
								Ezekiel<br />
								Daniel<br />
								Hosea<br />
								Joel<br />
								Amos<br />
								Obadiah<br />
								Jonah<br />
								Micah<br />
								Nahum<br />
								Habakkuk<br />
								Zephaniah<br />
								Haggai<br />
								Zechariah<br />
								Malachi
							</p>

						</td>
						<td valign="top" style="padding-left:10px">

							<p>
								<i>New Testament</i>
								<hr/>
								Matthew<br />
								Mark<br />
								Luke<br />
								John<br />
								Acts<br />
								Romans<br />
								1 Corinthians<br />
								2 Corinthians<br />
								Galatians<br />
								Ephesians<br />
								Philippians<br />
								Colossians<br />
								1 Thessalonians<br />
								2 Thessalonians<br />
								1 Timothy<br />
								2 Timothy<br />
								Titus<br />
								Philemon<br />
								Hebrews<br />
								James<br />
								1 Peter<br />
								2 Peter<br />
								1 John<br />
								2 John<br />
								3 John<br />
								Jude<br />
								Revelation
							</p>

						</td>
					</tr>
				</table>
				
			</td>
		</tr>
	</table>
	<?php

}

function yv_config_page() {

	// add youversion to plugins list in admin
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('YouVersion'), __('YouVersion'), 'manage_options', 'yv-config', 'yv_config');

}

// add a filter for all content to change youversion tagged text into links
add_filter('the_content', 'yv_generate_links');

// add youversion to plugins list in admin
add_action('admin_menu', 'yv_config_page');

?>
