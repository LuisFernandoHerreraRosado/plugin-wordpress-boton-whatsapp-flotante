<?php
/**
 * Plugin Name: WhatsApp Floating Button
 * Plugin URI:  https://example.com/whatsapp-floating-button
 * Description: Agrega un botón flotante de WhatsApp a tu sitio web con opciones configurables desde el panel de administración.
 * Version:     1.0.0
 * Author:      Jules
 * Author URI:  https://example.com
 * License:     GPL2
 * Text Domain: whatsapp-floating-button
 */

// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Definir constantes del plugin
define( 'WFB_PATH', plugin_dir_path( __FILE__ ) );
define( 'WFB_URL', plugin_dir_url( __FILE__ ) );
define( 'WFB_VERSION', '1.0.0' );

/**
 * La clase principal del plugin
 */
class WhatsApp_Floating_Button {

    public function __construct() {
        // Hooks para el panel de administración
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Hooks para el frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_button' ) );

        // Shortcode
        add_shortcode( 'whatsapp_button', array( $this, 'shortcode_handler' ) );

        // Widget
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
    }

    public function register_widget() {
        register_widget( 'WhatsApp_Button_Widget' );
    }

    /**
     * Añadir menú en el panel de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            'WhatsApp Button',
            'WhatsApp Button',
            'manage_options',
            'whatsapp-floating-button',
            array( $this, 'settings_page_html' ),
            'dashicons-whatsapp',
            65
        );
    }

    /**
     * Registrar ajustes usando Settings API
     */
    public function register_settings() {
        register_setting( 'wfb_settings_group', 'wfb_settings', array( $this, 'sanitize_settings' ) );

        add_settings_section(
            'wfb_main_section',
            'Configuración General',
            null,
            'whatsapp-floating-button'
        );

        add_settings_field(
            'wfb_phone',
            'Número de WhatsApp',
            array( $this, 'field_phone_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_message',
            'Mensaje Predeterminado',
            array( $this, 'field_message_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_text',
            'Texto del Botón',
            array( $this, 'field_text_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_show',
            'Mostrar Botón',
            array( $this, 'field_show_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_position',
            'Posición',
            array( $this, 'field_position_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_color',
            'Color del Botón',
            array( $this, 'field_color_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_size',
            'Tamaño (px)',
            array( $this, 'field_size_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_field(
            'wfb_include_url',
            'Incluir URL en el mensaje',
            array( $this, 'field_include_url_html' ),
            'whatsapp-floating-button',
            'wfb_main_section'
        );

        add_settings_section(
            'wfb_shortcode_section',
            'Configuración para Shortcodes y Widgets',
            null,
            'whatsapp-floating-button'
        );

        add_settings_field(
            'wfb_shortcode_color',
            'Color Predeterminado',
            array( $this, 'field_shortcode_color_html' ),
            'whatsapp-floating-button',
            'wfb_shortcode_section'
        );

        add_settings_field(
            'wfb_shortcode_size',
            'Tamaño Predeterminado (px)',
            array( $this, 'field_shortcode_size_html' ),
            'whatsapp-floating-button',
            'wfb_shortcode_section'
        );

        add_settings_field(
            'wfb_shortcode_text',
            'Texto Predeterminado',
            array( $this, 'field_shortcode_text_html' ),
            'whatsapp-floating-button',
            'wfb_shortcode_section'
        );

        add_settings_field(
            'wfb_shortcode_phone',
            'Teléfono Predeterminado',
            array( $this, 'field_shortcode_phone_html' ),
            'whatsapp-floating-button',
            'wfb_shortcode_section'
        );

        add_settings_field(
            'wfb_shortcode_message',
            'Mensaje Predeterminado',
            array( $this, 'field_shortcode_message_html' ),
            'whatsapp-floating-button',
            'wfb_shortcode_section'
        );
    }

    /**
     * Sanitizar los datos guardados
     */
    public function sanitize_settings( $input ) {
        $new_input = array();
        $new_input['phone'] = sanitize_text_field( $input['phone'] );
        $new_input['message'] = sanitize_text_field( $input['message'] );
        $new_input['text'] = sanitize_text_field( $input['text'] );
        $new_input['show'] = isset( $input['show'] ) ? 1 : 0;
        $new_input['position'] = in_array( $input['position'], array( 'right', 'left' ) ) ? $input['position'] : 'right';
        $new_input['color'] = sanitize_hex_color( $input['color'] );
        $new_input['size'] = absint( $input['size'] );
        $new_input['include_url'] = isset( $input['include_url'] ) ? 1 : 0;
        $new_input['shortcode_color'] = sanitize_hex_color( $input['shortcode_color'] );
        $new_input['shortcode_size'] = absint( $input['shortcode_size'] );
        $new_input['shortcode_text'] = sanitize_text_field( $input['shortcode_text'] );
        $new_input['shortcode_phone'] = sanitize_text_field( $input['shortcode_phone'] );
        $new_input['shortcode_message'] = sanitize_text_field( $input['shortcode_message'] );

        return $new_input;
    }

    // Funciones para renderizar los campos de entrada
    public function field_phone_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['phone'] ) ? $options['phone'] : '';
        echo "<input type='text' name='wfb_settings[phone]' value='" . esc_attr( $value ) . "' placeholder='Ej: 34600000000'>";
    }

    public function field_message_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['message'] ) ? $options['message'] : '';
        echo "<input type='text' name='wfb_settings[message]' value='" . esc_attr( $value ) . "' class='regular-text'>";
    }

    public function field_text_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['text'] ) ? $options['text'] : '';
        echo "<input type='text' name='wfb_settings[text]' value='" . esc_attr( $value ) . "' placeholder='Escríbenos'>";
    }

    public function field_show_html() {
        $options = get_option( 'wfb_settings' );
        $checked = isset( $options['show'] ) && $options['show'] ? 'checked' : '';
        echo "<input type='checkbox' name='wfb_settings[show]' value='1' $checked>";
    }

    public function field_position_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['position'] ) ? $options['position'] : 'right';
        ?>
        <select name="wfb_settings[position]">
            <option value="right" <?php selected( $value, 'right' ); ?>>Derecha</option>
            <option value="left" <?php selected( $value, 'left' ); ?>>Izquierda</option>
        </select>
        <?php
    }

    public function field_color_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['color'] ) ? $options['color'] : '#25D366';
        echo "<input type='color' name='wfb_settings[color]' value='" . esc_attr( $value ) . "'>";
    }

    public function field_size_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['size'] ) ? $options['size'] : '60';
        echo "<input type='number' name='wfb_settings[size]' value='" . esc_attr( $value ) . "' min='40' max='100'>";
    }

    public function field_include_url_html() {
        $options = get_option( 'wfb_settings' );
        $checked = isset( $options['include_url'] ) && $options['include_url'] ? 'checked' : '';
        echo "<input type='checkbox' name='wfb_settings[include_url]' value='1' $checked>";
        echo "<p class='description'>Si se activa, se añadirá la URL de la página actual al final del mensaje.</p>";
    }

    public function field_shortcode_color_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['shortcode_color'] ) ? $options['shortcode_color'] : '#25D366';
        echo "<input type='color' name='wfb_settings[shortcode_color]' value='" . esc_attr( $value ) . "'>";
    }

    public function field_shortcode_size_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['shortcode_size'] ) ? $options['shortcode_size'] : '60';
        echo "<input type='number' name='wfb_settings[shortcode_size]' value='" . esc_attr( $value ) . "' min='30' max='100'>";
    }

    public function field_shortcode_text_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['shortcode_text'] ) ? $options['shortcode_text'] : '';
        echo "<input type='text' name='wfb_settings[shortcode_text]' value='" . esc_attr( $value ) . "' placeholder='Escríbenos'>";
    }

    public function field_shortcode_phone_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['shortcode_phone'] ) ? $options['shortcode_phone'] : '';
        echo "<input type='text' name='wfb_settings[shortcode_phone]' value='" . esc_attr( $value ) . "' placeholder='Ej: 34600000000'>";
        echo "<p class='description'>Si se deja vacío, usará el teléfono de la configuración general.</p>";
    }

    public function field_shortcode_message_html() {
        $options = get_option( 'wfb_settings' );
        $value = isset( $options['shortcode_message'] ) ? $options['shortcode_message'] : '';
        echo "<input type='text' name='wfb_settings[shortcode_message]' value='" . esc_attr( $value ) . "' class='regular-text'>";
        echo "<p class='description'>Si se deja vacío, usará el mensaje de la configuración general.</p>";
    }

    /**
     * Renderizar la página de ajustes
     */
    public function settings_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wfb_settings_group' );
                do_settings_sections( 'whatsapp-floating-button' );
                submit_button( 'Guardar Ajustes' );
                ?>
            </form>

            <hr>
            <div style="background-color: #f0f0f1; padding: 20px; border-left: 4px solid #25D366; margin-top: 20px;">
            <h2>Uso del Shortcode</h2>
            <p>Puedes usar el siguiente shortcode para insertar el botón de WhatsApp en cualquier página, entrada o widget (compatible con Elementor):</p>
            <p style="font-size: 1.2em;"><code>[whatsapp_button]</code></p>

            <h3>Atributos disponibles:</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Atributo</th>
                        <th>Descripción</th>
                        <th>Valor por defecto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>phone</code></td>
                        <td>Número de teléfono con prefijo (ej: 34600000000)</td>
                        <td>El configurado en los ajustes generales</td>
                    </tr>
                    <tr>
                        <td><code>message</code></td>
                        <td>Mensaje que se enviará por defecto</td>
                        <td>El configurado en los ajustes generales</td>
                    </tr>
                    <tr>
                        <td><code>text</code></td>
                        <td>Texto que se muestra al pasar el ratón</td>
                        <td>El configurado en los ajustes generales</td>
                    </tr>
                    <tr>
                        <td><code>color</code></td>
                        <td>Color de fondo del botón (ej: #25D366)</td>
                        <td>El configurado en los ajustes generales</td>
                    </tr>
                    <tr>
                        <td><code>size</code></td>
                        <td>Tamaño del botón en píxeles</td>
                        <td>El configurado en los ajustes generales</td>
                    </tr>
                    <tr>
                        <td><code>include_url</code></td>
                        <td>Incluir la URL actual en el mensaje (1 = sí, 0 = no)</td>
                        <td>El configurado en los ajustes generales</td>
                    </tr>
                </tbody>
            </table>

            <p><strong>Ejemplo de uso avanzado:</strong></p>
            <p><code>[whatsapp_button phone="34600000000" message="Hola, vengo de la web" text="Contactar" color="#075e54" size="50" include_url="1"]</code></p>
            </div>
        </div>
        <?php
    }

    /**
     * Cargar assets
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'wfb-style', WFB_URL . 'assets/css/style.css', array(), WFB_VERSION );
        wp_enqueue_script( 'wfb-script', WFB_URL . 'assets/js/script.js', array(), WFB_VERSION, true );
    }

    /**
     * Obtener la URL de WhatsApp configurada
     */
    public static function get_whatsapp_url( $phone, $message, $include_url ) {
        if ( empty( $phone ) ) {
            return '';
        }

        $url = "https://wa.me/" . preg_replace( '/[^0-9]/', '', $phone );

        if ( $include_url ) {
            $current_url = is_singular() ? get_permalink() : '';
            if ( empty( $current_url ) && isset( $GLOBALS['wp'] ) ) {
                $current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
            }
            if ( ! empty( $current_url ) ) {
                $message .= ( ! empty( $message ) ? "\n" : "" ) . "(Enviado desde: " . $current_url . ")";
            }
        }

        if ( ! empty( $message ) ) {
            $url .= "?text=" . rawurlencode( $message );
        }

        return $url;
    }

    /**
     * Generar el HTML del botón
     */
    public static function get_button_html( $args ) {
        $phone       = isset( $args['phone'] ) ? $args['phone'] : '';
        $message     = isset( $args['message'] ) ? $args['message'] : '';
        $text        = isset( $args['text'] ) ? $args['text'] : '';
        $include_url = isset( $args['include_url'] ) ? (bool) $args['include_url'] : false;
        $color       = isset( $args['color'] ) ? $args['color'] : '#25D366';
        $size        = isset( $args['size'] ) ? $args['size'] : '60';
        $is_floating = isset( $args['is_floating'] ) ? (bool) $args['is_floating'] : false;
        $pos         = isset( $args['position'] ) ? $args['position'] : 'right';

        $url = self::get_whatsapp_url( $phone, $message, $include_url );

        if ( empty( $url ) ) {
            return '';
        }

        $class = $is_floating ? 'wfb-floating-button' : 'wfb-inline-button';
        $style = "background-color: {$color}; width: {$size}px; height: {$size}px; display: flex; align-items: center; justify-content: center; border-radius: 50%; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); overflow: visible !important;";

        if ( $is_floating ) {
            $style .= " position: fixed; bottom: 20px; {$pos}: 20px; z-index: 9999;";
        } else {
            $style .= " position: relative; display: inline-flex;";
        }

        ob_start();
        ?>
        <a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>" target="_blank" rel="nofollow" style="<?php echo esc_attr( $style ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="white" style="width: 60%; height: 60%; display: block; fill: white !important;">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.1 0-65.6-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.4-8.6-44.5-27.4-16.4-14.6-27.5-32.8-30.7-38.4-3.2-5.6-.3-8.6 2.5-11.4 2.5-2.5 5.5-6.5 8.3-9.7 2.8-3.2 3.7-5.5 5.5-9.2 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.8 23.5 9.2 31.5 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.5 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
            </svg>
            <?php if ( ! empty( $text ) ) : ?>
                <span class="wfb-text"><?php echo esc_html( $text ); ?></span>
            <?php endif; ?>
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * Handler para el shortcode [whatsapp_button]
     */
    public function shortcode_handler( $atts ) {
        $options = get_option( 'wfb_settings' );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        $atts = shortcode_atts( array(
            'phone'       => ! empty( $options['shortcode_phone'] ) ? $options['shortcode_phone'] : (isset($options['phone']) ? $options['phone'] : ''),
            'message'     => ! empty( $options['shortcode_message'] ) ? $options['shortcode_message'] : (isset($options['message']) ? $options['message'] : ''),
            'text'        => isset( $options['shortcode_text'] ) ? $options['shortcode_text'] : (isset($options['text']) ? $options['text'] : ''),
            'include_url' => isset( $options['include_url'] ) ? $options['include_url'] : 0,
            'color'       => ! empty( $options['shortcode_color'] ) ? $options['shortcode_color'] : '#25D366',
            'size'        => ! empty( $options['shortcode_size'] ) ? $options['shortcode_size'] : '60',
        ), $atts, 'whatsapp_button' );

        // Si no hay teléfono configurado ni en el shortcode, el botón no se mostrará.
        // Aseguramos que haya al menos un valor.
        if ( empty( $atts['phone'] ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<div style="background:#fff2f2;border:1px solid #ffa0a0;padding:10px;color:#d63638;"><strong>WhatsApp Button Error:</strong> Falta el número de teléfono en los ajustes o en el shortcode.</div>';
            }
            return '<!-- WhatsApp Button Shortcode: Falta el número de teléfono -->';
        }

        $args = array(
            'phone'       => $atts['phone'],
            'message'     => $atts['message'],
            'text'        => $atts['text'],
            'include_url' => $atts['include_url'],
            'color'       => $atts['color'],
            'size'        => $atts['size'],
            'is_floating' => false
        );

        return self::get_button_html( $args );
    }

    public function render_button() {
        $options = get_option( 'wfb_settings' );

        if ( ! isset( $options['show'] ) || ! $options['show'] ) {
            return;
        }

        $args = array(
            'phone'       => isset( $options['phone'] ) ? $options['phone'] : '',
            'message'     => isset( $options['message'] ) ? $options['message'] : '',
            'text'        => isset( $options['text'] ) ? $options['text'] : '',
            'include_url' => isset( $options['include_url'] ) ? $options['include_url'] : 0,
            'position'    => isset( $options['position'] ) ? $options['position'] : 'right',
            'color'       => isset( $options['color'] ) ? $options['color'] : '#25D366',
            'size'        => isset( $options['size'] ) ? $options['size'] : '60',
            'is_floating' => true
        );

        echo self::get_button_html( $args );
    }

}

/**
 * Clase para el Widget de WhatsApp
 */
class WhatsApp_Button_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'whatsapp_button_widget',
            'WhatsApp Button',
            array( 'description' => 'Añade un botón de WhatsApp a tus barras laterales.' )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        $options = get_option( 'wfb_settings' );

        $button_args = array(
            'phone'       => ! empty( $instance['phone'] ) ? $instance['phone'] : (! empty($options['shortcode_phone']) ? $options['shortcode_phone'] : (isset($options['phone']) ? $options['phone'] : '')),
            'message'     => ! empty( $instance['message'] ) ? $instance['message'] : (! empty($options['shortcode_message']) ? $options['shortcode_message'] : (isset($options['message']) ? $options['message'] : '')),
            'text'        => ! empty( $instance['text'] ) ? $instance['text'] : (isset($options['shortcode_text']) ? $options['shortcode_text'] : (isset($options['text']) ? $options['text'] : '')),
            'include_url' => isset( $instance['include_url'] ) ? $instance['include_url'] : (isset($options['include_url']) ? $options['include_url'] : 0),
            'color'       => ! empty( $instance['color'] ) ? $instance['color'] : (isset($options['shortcode_color']) ? $options['shortcode_color'] : '#25D366'),
            'size'        => ! empty( $instance['size'] ) ? $instance['size'] : (isset($options['shortcode_size']) ? $options['shortcode_size'] : '60'),
            'is_floating' => false
        );

        echo WhatsApp_Floating_Button::get_button_html( $button_args );

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $phone       = ! empty( $instance['phone'] ) ? $instance['phone'] : '';
        $message     = ! empty( $instance['message'] ) ? $instance['message'] : '';
        $text        = ! empty( $instance['text'] ) ? $instance['text'] : '';
        $include_url = isset( $instance['include_url'] ) ? (bool) $instance['include_url'] : true;
        $options     = get_option( 'wfb_settings' );
        $color       = ! empty( $instance['color'] ) ? $instance['color'] : (isset($options['shortcode_color']) ? $options['shortcode_color'] : '#25D366');
        $size        = ! empty( $instance['size'] ) ? $instance['size'] : (isset($options['shortcode_size']) ? $options['shortcode_size'] : '60');
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>">Número de WhatsApp:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'phone' ) ); ?>" type="text" value="<?php echo esc_attr( $phone ); ?>" placeholder="Ej: 34600000000">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'message' ) ); ?>">Mensaje:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'message' ) ); ?>" type="text" value="<?php echo esc_attr( $message ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>">Texto:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $include_url ); ?> id="<?php echo esc_attr( $this->get_field_id( 'include_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'include_url' ) ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id( 'include_url' ) ); ?>">Incluir URL</label>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>">Color:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color' ) ); ?>" type="color" value="<?php echo esc_attr( $color ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>">Tamaño (px):</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" type="number" value="<?php echo esc_attr( $size ); ?>" min="40" max="100">
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['phone']       = sanitize_text_field( $new_instance['phone'] );
        $instance['message']     = sanitize_text_field( $new_instance['message'] );
        $instance['text']        = sanitize_text_field( $new_instance['text'] );
        $instance['include_url'] = ! empty( $new_instance['include_url'] ) ? 1 : 0;
        $instance['color']       = sanitize_hex_color( $new_instance['color'] );
        $instance['size']        = absint( $new_instance['size'] );
        return $instance;
    }
}

// Inicializar el plugin
new WhatsApp_Floating_Button();
