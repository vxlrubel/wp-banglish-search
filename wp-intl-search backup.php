<?php
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
    // Multi-character patterns for phonetic transliteration
    $replacements = [
        // Common Banglish syllables and sounds
        'th' => 'থ', 'dh' => 'ধ', 'sh' => 'শ', 'ch' => 'চ', 'kh' => 'খ',
        'ph' => 'ফ', 'bh' => 'ভ', 'ng' => 'ঙ', 'jh' => 'ঝ', 'tr' => 'ত্র',
        'dr' => 'দ্র', 'kr' => 'ক্র', 'gr' => 'গ্র', 'pr' => 'প্র',
        'br' => 'ব্র', 'st' => 'স্ত', 'sp' => 'স্প', 'sk' => 'স্ক',
    
        // Vowel combinations and vowel sounds
        'aa' => 'আ', 'ee' => 'ঈ', 'ii' => 'ঈ', 'oo' => 'ও', 'uu' => 'ঊ',
        'oi' => 'ঐ', 'ou' => 'ঔ', 'ei' => 'এ', 'oi' => 'ওই', 'au' => 'আউ',
    
        // Bangla sound and diphthongs
        'ar' => 'আর', 'ur' => 'উর', 'ir' => 'ইর', 'or' => 'ওর', 'ur' => 'উর',
        'an' => 'অন', 'en' => 'এন', 'in' => 'ইন', 'on' => 'অন', 'un' => 'উন',
    
        // Common two-letter syllables
        'ba' => 'বা', 'be' => 'বে', 'bo' => 'বো', 'bi' => 'বি',
        'pa' => 'পা', 'pe' => 'পে', 'po' => 'পো', 'pi' => 'পি',
        'ka' => 'কা', 'ke' => 'কে', 'ko' => 'কো', 'ki' => 'কি',
        'ta' => 'তা', 'te' => 'তে', 'to' => 'তো', 'ti' => 'তি',
        'la' => 'লা', 'le' => 'লে', 'lo' => 'লো', 'li' => 'লি',
        'na' => 'না', 'ne' => 'নে', 'ni' => 'নি', 'no' => 'নো',/*  */
    
        // Common ending patterns
        'ar' => 'আর', 'er' => 'এর', 'ir' => 'ইর', 'ur' => 'উর',
        'n' => 'ন', 'm' => 'ম', 'b' => 'ব', 'p' => 'প', 'r' => 'র',
    
        // Single-letter replacements (fallback for characters not covered above)
        'a' => 'অ', 'b' => 'ব', 'c' => 'ক', 'd' => 'দ', 'e' => 'এ',
        'f' => 'ফ', 'g' => 'গ', 'h' => 'হ', 'i' => 'ই', 'j' => 'জ',
        'k' => 'ক', 'l' => 'ল', 'm' => 'ম', 'n' => 'ন', 'o' => 'ও',
        'p' => 'প', 'q' => 'ক', 'r' => 'র', 's' => 'স', 't' => 'ত',
        'u' => 'উ', 'v' => 'ভ', 'w' => 'ও', 'x' => 'ক্স', 'y' => 'য',
        'z' => 'জ',
    
        // Banglish specific words or short forms that need specific mapping
        'beta' => 'বেটা', 'bhalo' => 'ভালো', 'tumi' => 'তুমি', 'apni' => 'আপনি',
        'shob' => 'সব', 'ekta' => 'একটা', 'choto' => 'ছোট', 'boro' => 'বড়',
    
        // Special Banglish words and common names
        'jhalmuri' => 'ঝালমুড়ি', 'panta' => 'পান্তা', 'maacher' => 'মাছের',
    
        // Common phrases
        'khub' => 'খুব', 'kichu' => 'কিছু', 'tumi ki' => 'তুমি কি',
    
        // Bengali phonetic words that could be part of the Banglish input
        'amra' => 'আমরা', 'tomra' => 'তোমরা', 'se' => 'সে', 'tara' => 'তারা',
        'kintu' => 'কিন্তু', 'jodi' => 'যদি', 'kemon' => 'কেমন',
    ];
    

    // Replace longer patterns first to ensure accurate transliteration
    uksort($replacements, function($a, $b) {
        return strlen($b) - strlen($a);
    });

    // Apply replacements to the input string
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

