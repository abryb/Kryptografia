<?php

$text = <<<TEXT
tfoudq wnsq xmnvqfq agdtkenpfot gr sqz rvxrmotlznei rvxrmotlztug votax q hgmfotp mglzqsq mqqrqhzgvqfq hkmtm oflznzxept hqflzvgvt votsx akqpgv hgremql rkxuotp vgpfn lvoqzgvtp dqlmnfq zq wnłq vnagkmnlznvqfq usgvfot hkmtm losn mwkgpft gkqm offt lsxmwn hqflzvgvt o vnvoqrgvemt fotdote qst zqamt offnei hqflzv tfoudq fqstmqsq rg kgrmofn tstazkgdteiqfoemfnei vokfoagvnei dqlmnf lmnykxpqenei o wnłq hkgrxagvqfq v votsx kgmfnei grdoqfqei hg kqm hotkvlmn lmnykgukqdn mqagrgvqft hkmn hgdgen tfoudn xrqsg lot kgmlmnykgvqe hgslaod aknhzgsgugd v zkmnrmotlznd rkxuod kgax hkqet hgsqagv usgvfot dqkoqfq ktptvlaotug ptkmtug kgmneaotug o itfknaq mnuqslaotug hgmvgsosn fq rqslmt hkqet fqr rtagrgvqfotd lmnykgv lzqst xfgvgemtlfoqfnei dqlmnf tfoudq fqphotkv v hgslet q hg vnwxeix vgpfn vt ykqfepo o votsaotp wknzqfoo
TEXT;

$alphabet = "abcdefghijklmnoprstuwyz";

for ($i = 0; $i < strlen($alphabet); $i ++) {

    echo implode(array_map(function($char) use ($i, $alphabet) {
        if (($pos = strpos($char, $alphabet)) !== false) {
            return $alphabet[($pos + $i)%strlen($alphabet)];
        }
        return $char;
    }, str_split($text)));
    echo PHP_EOL;
}