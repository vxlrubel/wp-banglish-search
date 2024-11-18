<?php
/*
Plugin Name: WP Banglish Search Plugin
Description: A WordPress plugin that checks for the PHP Intl extension and provides search functionality by converting Banglish input to Bengali.
Version: 1.1
Author: Your Name
*/

// Register activation hook to check if the Intl extension is enabled
register_activation_hook(__FILE__, 'wp_banglish_search_check');

function wp_banglish_search_check() {
    if (!extension_loaded('intl')) {
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate the plugin
        wp_die(
            'The WP Banglish Search Plugin requires the PHP Intl extension to be installed and enabled. 
            Please enable it in your PHP configuration and try again. 
            <a href="' . admin_url('plugins.php') . '">Return to Plugins</a>'
        );
    }
}

// Add admin notice if Intl is not loaded
add_action('admin_notices', 'wp_banglish_search_admin_notice');

function wp_banglish_search_admin_notice() {
    if (!extension_loaded('intl')) {
        echo '<div class="notice notice-error is-dismissible">
            <p><strong>Warning:</strong> The PHP Intl extension is not enabled. This plugin requires the Intl extension to work correctly. 
            Please contact your hosting provider or enable it in your PHP configuration.</p>
        </div>';
    }
}

// Custom function to convert Banglish to Bengali
function convert_banglish_to_bengali($input) {
    $replacements = [
        // Vowel and consonant combinations for 'a', 'b', 'c', etc.
        'aa' => 'আ', 'ba' => 'বা', 'ca' => 'চা', 'da' => 'দা', 'ea' => 'এ',
        'fa' => 'ফা', 'ga' => 'গা', 'ha' => 'হা', 'ia' => 'ই', 'ja' => 'জা',
        'ka' => 'কা', 'la' => 'লা', 'ma' => 'মা', 'na' => 'না', 'oa' => 'ও',
        'pa' => 'পা', 'qa' => 'কা', 'ra' => 'রা', 'sa' => 'সা', 'ta' => 'তা',
        'ua' => 'উ', 'va' => 'ভা', 'wa' => 'ও', 'xa' => 'ক্সা', 'ya' => 'যা', 'za' => 'জা',

        // 'e' combinations (added 'be', 'ce', 'de' as requested)
        'ae' => 'এ', 'be' => 'বে', 'ce' => 'চে', 'de' => 'দে', 'ee' => 'ঈ',
        'fe' => 'ফে', 'ge' => 'গে', 'he' => 'হে', 'ie' => 'ই', 'je' => 'জে',
        'ke' => 'কে', 'le' => 'লে', 'me' => 'মে', 'ne' => 'নে', 'oe' => 'ও',
        'pe' => 'পে', 'qe' => 'কে', 're' => 'রে', 'se' => 'সে', 'te' => 'তে',
        'ue' => 'ঊ', 've' => 'ভে', 'we' => 'ও', 'xe' => 'ক্সে', 'ye' => 'যে', 'ze' => 'জে',

        // 'i' combinations
        'ai' => 'আই', 'bi' => 'বি', 'ci' => 'চি', 'di' => 'দি', 'ei' => 'ই',
        'fi' => 'ফি', 'gi' => 'গি', 'hi' => 'হি', 'ii' => 'ঈ', 'ji' => 'জি',
        'ki' => 'কি', 'li' => 'লি', 'mi' => 'মি', 'ni' => 'নি', 'oi' => 'ঐ',
        'pi' => 'পি', 'qi' => 'কী', 'ri' => 'রি', 'si' => 'সী', 'ti' => 'তি',
        'ui' => 'ঊ', 'vi' => 'ভি', 'wi' => 'ও', 'xi' => 'ক্সি', 'yi' => 'যি', 'zi' => 'জি',

        // 'o' combinations
        'ao' => 'অও', 'bo' => 'বো', 'co' => 'চো', 'do' => 'দো', 'eo' => 'এ',
        'fo' => 'ফো', 'go' => 'গো', 'ho' => 'হো', 'io' => 'ইও', 'jo' => 'জো',
        'ko' => 'কো', 'lo' => 'লো', 'mo' => 'মো', 'no' => 'নো', 'oo' => 'ও',
        'po' => 'পো', 'qo' => 'কো', 'ro' => 'রো', 'so' => 'সো', 'to' => 'তো',
        'uo' => 'উ', 'vo' => 'ভো', 'wo' => 'ও', 'xo' => 'ক্সো', 'yo' => 'যো', 'zo' => 'জো',

        // 'u' combinations
        'au' => 'আউ', 'bu' => 'বু', 'cu' => 'চু', 'du' => 'দু', 'eu' => 'এ',
        'fu' => 'ফু', 'gu' => 'গু', 'hu' => 'হু', 'iu' => 'ঈ', 'ju' => 'জু',
        'ku' => 'কু', 'lu' => 'লু', 'mu' => 'মু', 'nu' => 'নু', 'ou' => 'ঔ',
        'pu' => 'পু', 'qu' => 'কু', 'ru' => 'রু', 'su' => 'সু', 'tu' => 'তু',
        'uu' => 'ঊ', 'vu' => 'ভু', 'wu' => 'ও', 'xu' => 'ক্সু', 'yu' => 'যু', 'zu' => 'জু',

        // Single character mappings
        'a' => 'অ', 'b' => 'ব', 'c' => 'ক', 'd' => 'দ', 'e' => 'এ',
        'f' => 'ফ', 'g' => 'গ', 'h' => 'হ', 'i' => 'ই', 'j' => 'জ',
        'k' => 'ক', 'l' => 'ল', 'm' => 'ম', 'n' => 'ন', 'o' => 'ও',
        'p' => 'প', 'q' => 'ক', 'r' => 'র', 's' => 'স', 't' => 'ত',
        'u' => 'উ', 'v' => 'ভ', 'w' => 'ও', 'x' => 'ক্স', 'y' => 'য',
        'z' => 'জ',
    ];

    // Sort by length of key in descending order to replace larger patterns first
    uksort($replacements, function($a, $b) {
        return strlen($b) - strlen($a);
    });

    // Replace Banglish patterns with Bengali script
    $output = str_ireplace(array_keys($replacements), array_values($replacements), $input);

    return $output;
}


// Filter to enhance the search query with custom Banglish to Bengali conversion
add_filter('pre_get_posts', 'wp_banglish_search_filter');

function wp_banglish_search_filter($query) {
    if ($query->is_search && !is_admin()) {
        $search_term = $query->query_vars['s'];

        if ($search_term) {
            $converted_term = convert_banglish_to_bengali($search_term);
            if ($converted_term) {
                $query->query_vars['s'] = $converted_term;
            }
        }
    }
    return $query;
}

// Function to generate a search form with a shortcode
function wp_banglish_search_form_shortcode() {
    ob_start(); // Start output buffering
    ?>
    <form role="search" method="get" id="searchform" class="searchform" action="<?php echo home_url('/'); ?>">
        <label class="screen-reader-text" for="s">Search for:</label>
        <input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="Search using Banglish..." />
        <input type="submit" id="searchsubmit" value="Search" />
    </form>
    <?php
    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('intl_search_form', 'wp_banglish_search_form_shortcode');
?>
