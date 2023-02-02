<?php
namespace App\Helpers;

use Carbon\Carbon;

class TimeHelper {
    public function offsetDifference ($originTz = NULL, $remoteTz, $offsetIn = 'second') {
        if ($originTz === null) {
            if(!is_string($originTz = date_default_timezone_get())) {
                return false;
            }
        }
        $originDtz = Carbon::now()->timezone($originTz)->getOffset();
        $remoteDtz = Carbon::now()->timezone($remoteTz)->getOffset();
        $offset     = $originDtz - $remoteDtz;

        switch ($offsetIn) {
            case 'hour':
                $hour = floor($offset / 3600);
                $minute = ($offset % 3600) / 60;
                return $hour.":".$minute;
                break;
            case 'minute':
                return $offset / 60;
                break;
            case 'second':
            default:
                return $offset;
                break;
        }
    }

    public function convertTz ($timestamp, $originTz = NULL, $remoteTz) {
        if ($originTz === null) {
            if(!is_string($originTz = date_default_timezone_get())) {
                return false;
            }
        }

        $originOffsetSecond = Carbon::now()->timezone($originTz)->getOffset();
        $hour = floor($originOffsetSecond / 3600);
        $minute = ($originOffsetSecond % 3600) / 60;
        $originOffset = $hour.":".$minute;

        $time = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
        $times = $time.($originOffset >= 0 ? '+'.$originOffset : '-'.$originOffset);
        $newTimes = Carbon::parse($times, $originTz)->setTimezone($remoteTz)->format('c');

        // return gettype(Carbon::parse($timestamp)->format('Y-m-d H:i:s'));
        // return $times;
        // return $originOffset.' : '.$newTimes;
        return $newTimes;
        // return $originTz;
    }
}
