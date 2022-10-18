<?php
/**
 * @version
 * @package
 * @author Aleksey Tikhomirov
 * @copyright 24.02.2019
 */

class DeliveryTerminals
{

    public $terminals;
    public $user_terminal;
    public $current_terminal;
    /**
     * IikoShortCodes constructor.
     */
    public function __construct()
    {
        if(false !== IikoApi::getDeliveryTerminal()){
            $this->terminals = IikoApi::getDeliveryTerminal();
            $this->user_terminal = get_option('iiko_terminal');
            $this->set_shortcodes();
        }
    }


    /**
     * Получить ID Ресторана
     * @return string
     */
    public function get_organizationId(){
      return $this->current_terminal['organizationId'];
    }

    /**
     * Название Ресторна Доставки
     */
    public function get_deliveryRestaurantName(){
        return $this->current_terminal['deliveryRestaurantName'];
    }

    /**
     * ID терминала
     */
    public function get_deliveryTerminalId(){
        return $this->current_terminal['deliveryTerminalId'];
    }

    /**
     * Название
     */
    public function get_name(){
        return $this->current_terminal['name'];
    }

    /**
     * Адрес
     */
    public function get_address(){
        return $this->current_terminal['address'];
    }

    /**
     * Режим работы
     *
     * @param $atts
     * @return string
     */
    public function get_openingHours($atts){
        $atts = shortcode_atts( array(
            'return' => 'html',
            'view'   => 'table',
        ), $atts );
        $html = $separator = '';

        if('table' === $atts['view']){
            $separator = '<br>';
        } else {
            $separator = ' ';
        }

        if('html' === $atts['return']){
            foreach ($this->current_terminal['openingHours'] as $day){
                $day = (object) $day;
                switch ($day->dayOfWeek) {
                    case 0 :
                        if(true === $day->allDay){
                            $html .= __('Пн-Вс', 'iiko') . ': ' . $day->from .' - ' . $day->to . $separator;
                        } elseif (true === $day->closed){
                            $html .= __('Пн', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Пн', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                    case 1 :
                        if (true === $day->closed){
                            $html .= __('Вт', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Вт', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                    case 2:
                        if (true === $day->closed){
                            $html .= __('Ср', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Ср', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                    case 3:
                        if (true === $day->closed){
                            $html .= __('Чт', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Чт', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                    case 4:
                        if (true === $day->closed){
                            $html .= __('Пт', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Пт', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                    case 5:
                        if (true === $day->closed){
                            $html .= __('Сб', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Сб', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                    case 6:
                        if (true === $day->closed){
                            $html .= __('Вс', 'iiko') . __('Закрыто','iiko');
                        } else {
                            $html .= __('Вс', 'iiko') . ': ' . $day->from . ' - ' . $day->to . $separator;
                        }
                        break;
                }
            }
            return $html;
        } else {
            return $this->current_terminal['openingHours'];
        }
    }

    /**
     * @return string - Номер ревизии сущности из РМС
     */
    public function get_externalRevision()
    {
        return $this->current_terminal['externalRevision'];
    }

    /**
     * Тех информация
     */
    public function get_technicalInformation(){
        return $this->current_terminal['technicalInformation'];
    }


    public function set_shortcodes(){
        if( empty($this->terminals) || false === $this->terminals) {
            return;
        }

       foreach ($this->terminals as $current_terminal) {
           if( $this->user_terminal === $current_terminal['deliveryTerminalId'] ) {
               $this->current_terminal = $current_terminal;
               add_shortcode('terminal_rest_name', [$this, 'get_deliveryRestaurantName']);
               add_shortcode('terminal_name', [$this, 'get_name']);
               add_shortcode('terminal_address', [$this, 'get_address']);
               add_shortcode('terminal_worktime', [$this, 'get_openingHours']);
           }
        }
    }


/*    public function get_TerminalWorkTime($atts){
        $worktime = $this->get_openingHours();
        var_dump($worktime);
        foreach ($worktime as $day){
           var_dump($day);
        }
        ob_start();
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }*/

}
