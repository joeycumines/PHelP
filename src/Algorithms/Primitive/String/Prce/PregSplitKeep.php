<?php

namespace JoeyCumines\Phelp\Algorithms\Primitive\String\Prce;

/**
 * Trait PregSplitKeep
 *
 * @package JoeyCumines\Phelp\Algorithms\Primitive\String
 *
 * An extension on `preg_split` to allow re-building the string after modifying it.
 */
trait PregSplitKeep
{
    /**
     * Split on a regex, keeping all the information necessary to rebuild the string. The result will be as
     * `preg_split` PREG_SPLIT_OFFSET_CAPTURE with the offset replaced with the actual string matched, stored on the
     * left side, meaning that the last value will have a null value at index 1.
     *
     * This method is multi-byte safe to the same level as `preg_split` (which is used directly).
     *
     * @param string $pattern
     * @param string $subject
     * @param int $limit
     *
     * @return array[] The split segments, like [string, string|null][]
     */
    private function pregSplitKeep($pattern, $subject, $limit = -1)
    {
        $result = preg_split($pattern, $subject, $limit, PREG_SPLIT_OFFSET_CAPTURE);

        foreach ($result as $k => $list) {
            $segment = (string)($list[0]);
            $offset = (int)($list[1]);
            $delimiter = null;

            $k = (int)$k;
            $nextK = $k + 1;
            if (true === array_key_exists($nextK, $result)) {
                $nextOffset = (int)($result[$nextK][1]);
                $segmentLen = strlen($segment);
                $delimiter = (string)substr($subject, $offset + $segmentLen, $nextOffset - $offset - $segmentLen);
            }

            $result[$k] = [$segment, $delimiter];
        }

        return $result;
    }
}
