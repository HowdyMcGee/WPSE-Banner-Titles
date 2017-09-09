# WPSE Banner Titles Plugin

Created specifically to answer a [WordPress Stack Exchange Question](https://wordpress.stackexchange.com/q/279493/7355).

This is a fairly simple WordPress standalone plugin that adds a meta-field underneath the main post title which can be used as a subtitle.
It also provides action hooks to display these titles in HTML. It's probably more complicated than it needs to be but fun none-the-less.

## How To Use

You can just call the following action hook passing the following parameters ( Default supplied ):

```
// Full action definition
do_action( 'wpse_banner_title', array(
    'wrapper'		=> 'h1',		// HTML element to wrap around the title
    'classes'		=> array(),		// Classes attached to HTML wrapper
    'attributes'	=> array(),		// Any additional attributes, ex: array( 'data-attr' => 'test' ) = data-attr="test"
    'before_title'	=> '',			// Any text / html before the actual title but inside wrapper
    'after_title'	=> '',			// Any text / html after the actual title but inside wrapper
    'top_title'		=> false,		// Whether or not to get ancestors title to show, false will be the current page title
    'subtitle'		=> false,		// Whether or not to show the subtitle field
) );

// Show the current page title
do_action( 'wpse_banner_title' );

// Show the current pages top most ancestors title
do_action( 'wpse_banner_title', array( 'top_title' => ture ) );

// Show the subtitle metadata, if it exists
do_action( 'wpse_banner_title', array( 'subtitle' => true ) );
```

The subtitle field will not display should there be no value in the Post Edit subtitle field.

## Available Filter Hooks

The plugin comes with a couple filter hooks listed below:

```
/**
 * Allow the user to modify the title should it be incorrect or otherwise
 */
apply_filters( 'wpse_modify_banner_title', String $title, Integer $post_id );


/**
 * List of acceptable post types to display subtitle metadata on
 */
apply_filters( 'wpse_subtitle_post_types', Array $post_types );
```
