<?php


namespace App\Services;


class FightLog
{
    /** @var string[] */
    private static $log;

    public static function write(string $message) {
        self::$log []= $message;
    }

    /** @return string[] */
    public static function read(): array {
        return self::$log;
    }

    public static function clear() {
        self::$log = [];
    }

    public static function getDamageString(int $damage): string {
        return self::num_decline($damage, ['единицу урона', 'единицы урона', 'единиц урона']);
    }

    public static function getRobotName(string $owner, int $case = 1): string {
        switch ("$owner.$case") {
            case 'vk.1':
                return 'ваш робот';
            case 'vk.2':
                return 'вашему роботу';
            case 'vk.3':
                return 'вашего робота';
            case 'case.1':
                return 'босс';
            case 'case.2':
                return 'боссу';
            case 'case.3':
                return 'босса';
        }
        return '';
    }

    private static function num_decline( $number, $titles, $show_number = 1 ): string {

        if( is_string( $titles ) )
            $titles = preg_split( '/, */', $titles );

        // когда указано 2 элемента
        if( empty( $titles[2] ) )
            $titles[2] = $titles[1];

        $cases = [ 2, 0, 1, 1, 1, 2 ];

        $intnum = abs( (int) strip_tags( $number ) );

        $title_index = ( $intnum % 100 > 4 && $intnum % 100 < 20 )
            ? 2
            : $cases[ min( $intnum % 10, 5 ) ];

        return ( $show_number ? "$number " : '' ) . $titles[ $title_index ];
    }
}
