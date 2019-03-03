<?php

$text = <<<TXT
tfoudq wnsq xmnvqfq agdtkenpfot gr sqz rvxrmotlznei rvxrmotlztug votax q hgmfotp mglzqsq mqqrqhzgvqfq hkmtm oflznzxept hqflzvgvt votsx akqpgv hgremql rkxuotp vgpfn lvoqzgvtp dqlmnfq zq wnłq vnagkmnlznvqfq usgvfot hkmtm losn mwkgpft gkqm offt lsxmwn hqflzvgvt o vnvoqrgvemt fotdote qst zqamt offnei hqflzv tfoudq fqstmqsq rg kgrmofn tstazkgdteiqfoemfnei vokfoagvnei dqlmnf lmnykxpqenei o wnłq hkgrxagvqfq v votsx kgmfnei grdoqfqei hg kqm hotkvlmn lmnykgukqdn mqagrgvqft hkmn hgdgen tfoudn xrqsg lot kgmlmnykgvqe hgslaod aknhzgsgugd v zkmnrmotlznd rkxuod kgax hkqet hgsqagv usgvfot dqkoqfq ktptvlaotug ptkmtug kgmneaotug o itfknaq mnuqslaotug hgmvgsosn fq rqslmt hkqet fqr rtagrgvqfotd lmnykgv lzqst xfgvgemtlfoqfnei dqlmnf tfoudq fqphotkv v hgslet q hg vnwxeix vgpfn vt ykqfepo o votsaotp wknzqfoo
TXT;
$frequencyAlphabet = [
    'a' => 2.00,
    'e' => 2.00,
    'o' => 2.00,
    'i' => 2.00,
    'z' => 2.00,
    'n' => 2.00,
    's' => 1.5,
    'r' => 1.5,
    'w' => 1.5,
    'c' => 1.5,
    'l' => 1.5,
    't' => 1.0,
    'y' => 1.0,
    'k' => 1.0,
    'd' => 1.0,
    'p' => 1.0,
    'm' => 1.0,
    'u' => 1.0,
    'j' => 1.0,
    'b' => 1.0,
    'g' => 1.0,
    'h' => 1.0,
    'f' => 0.60,
    'q' => 0.5,
    'v' => 0.1,
    'x' => 0.1,
];
$frequencyAlphabet = 3;
$hasToBe = [
    't' => ['e'],
    'f' => ['n'],
    'o' => ['i'],
    'u' => ['g'],
    'd' => ['m'],
    'q' => ['a'],
    'n' => ['y'],
    's' => ['l'],
    'x' => ['u'],
    'm' => ['z'],
    'w' => ['b'],
    'r' => ['d'],
    'a' => ['k'],
    'z' => ['t'],
    'v' => ['w'],
    'k' => ['r'],
    'e' => ['c'],
    'l' => ['s'],
    'p' => ['j'],
    'h' => ['p'],
//    'g' => ['h'],
];

$decryptor = new PolishDecrypt($text, $frequencyAlphabet, $hasToBe);

foreach ($decryptor->findDecryptions() as $p) {
    echo strtr($text, $p) .PHP_EOL;
}

// CLASSES
class PolishDecrypt
{

    private $text;
    private $frequencyAlphabet = [/* initialized in __constructor */];
    private $polishFrequency = [
        'a' => 9.90,
        'e' => 8.77,
        'o' => 8.60,
        'i' => 8.21,
        'z' => 6.53,
        'n' => 5.72,
        's' => 4.98,
        'r' => 4.69,
        'w' => 4.65,
        'c' => 4.36,
        'l' => 4.02,
        't' => 3.98,
        'y' => 3.76,
        'k' => 3.51,
        'd' => 3.25,
        'p' => 3.13,
        'm' => 2.80,
        'u' => 2.50,
        'j' => 2.28,
        'b' => 1.47,
        'g' => 1.42,
        'h' => 1.08,
        'f' => 0.30,
        'q' => 0.14,
        'v' => 0.04,
        'x' => 0.02,
    ];
    private $analysis = [/* initialized in constructor */]; // copy of $polishFrequency with another values;
    private $singleCharWords = ['a', 'i', 'o', 'z', 'w', 'u'];
    private $twoCharWords = <<<TXT
aa ad ag aj al ar as au az
ba be bo bu by
ci co
da do
eh ej er es et
fa fe fi fu
ge go
ha he hi hm ho hu
id ii il im iw iz
ja je
ki ko ku
la li lu
ma me mi mu my
na ni no
od oj ok om on op os ot oz
od os ow
pa pi po
re
sa se si su
ta te to ts tu ty
ud uf ul ut
we wy
za ze
ze
TXT;

    private $perms = [ /* 'q' => ['a', 'e'], ... */];
    private $hasToBe = [];

    public function __construct(string $text, $frequencyAlphabet, array $hasToBe)
    {
        $this->hasToBe = $hasToBe;
        $this->text = $text;
        if (is_array($frequencyAlphabet)) {
            $this->frequencyAlphabet = array_merge($this->frequencyAlphabet, $frequencyAlphabet);
        } else {
            $this->frequencyAlphabet = array_map(function ($v) use ($frequencyAlphabet){
                return $frequencyAlphabet ;
            }, $this->polishFrequency);
        }
        $this->analysis = array_map(function ($v) {
            return 0.0;
        }, $this->polishFrequency);
    }

    /**
     * @return Generator
     */
    public function findDecryptions()
    {
        $this->prepareStaticAnalysis();
        $this->prepareCharPermsWithGivenFrequencyDeviation();
        $this->validateCharPermsAreNotEmpty();
        $this->findSingleWordsAndFilterPermsWithIt();
        $this->findTwoCharWordsAndFilterPermsWithIt();
        $this->perms = array_merge($this->perms, $this->hasToBe);
        return $this->doPermute($this->perms);
    }

    private function prepareStaticAnalysis()
    {
        // Tworzenie analizy dla konkretnego tekstu
        $analysis = $this->analysis;
        foreach ($this->acceptableCharacters() as $char) {
            $analysis[$char] = substr_count($this->text, $char);
        }
        $sum = array_sum($analysis);

        foreach ($analysis as $char => $v) {
            $analysis[$char] = round(100 * $v / $sum, 2);
        }
        arsort($analysis);
        $this->analysis = $analysis;
    }

    private function prepareCharPermsWithGivenFrequencyDeviation()
    {
        $charsAcceptablePerms = [];
        foreach ($this->analysis as $char => $f) {
            $acceptable = [];
            foreach ($this->polishFrequency as $pChar => $pf) {
                $frq = $this->frequencyAlphabet[$pChar];
                if ($pf > $f - $frq && $pf < $f + $frq) {
                    $acceptable[] = $pChar;
                }
            }
            $charsAcceptablePerms[$char] = $acceptable;
        }
        $this->perms = $charsAcceptablePerms;
    }

    private function validateCharPermsAreNotEmpty()
    {
        foreach ($this->perms as $char => $perm) {
            if (empty($perm)) {
                throw new \InvalidArgumentException('Frequency deviation to small!');
            }
        }
    }

    private function findSingleWordsAndFilterPermsWithIt()
    {
        $matches = [];
        preg_match_all('#\s(\w)\s#', $this->text, $matches);
        $singleCharacters = array_unique($matches[1]);
        foreach ($singleCharacters as $singleCharacter) {
            $this->perms[$singleCharacter] = array_intersect($this->perms[$singleCharacter], $this->singleCharWords);
        }
    }

    private function orderPerms(array $perms)
    {
        uasort($perms, function ($a, $b) {
            return count($a) >= count($b);
        });
        return $perms;
    }

    public function doPermute(array $perms)
    {
        if (empty($perms)) {
            yield [];
        }
        $perms = $this->orderPerms($perms);
        foreach ($perms as $char => $charAcceptablePerms) {
            foreach ($charAcceptablePerms as $toChar) {
                $tmpPerms = $perms;
                $dictionary[$char] = $toChar;
                unset($tmpPerms[$char]);
                array_walk($tmpPerms, function (&$v) use ($toChar) {
                    $v = array_diff($v, [$toChar]);
                });

                if (empty($tmpPerms)) {
                    yield $dictionary;
                    continue;
                }
                $differentCount = count(array_unique(array_merge(...array_values($tmpPerms))));
                if (count($tmpPerms) > $differentCount) {
                    continue;
                }

                foreach ($this->doPermute($tmpPerms) as $x) {
                    $test = array_merge($dictionary, $x);
                    yield $test;
                }
            }
            break;
        }
    }

    private function findTwoCharWordsAndFilterPermsWithIt()
    {
        $t = preg_replace('#[^\ \w]#', ' ', $this->twoCharWords);
        $t = explode(' ',$t);
        $firstLetters = [];
        $secondLetters = [];
        foreach ($t as $word) {
            $firstLetters[] = $word[0];
            $secondLetters[] = $word[1];
        }

        $firstLetters = array_unique($firstLetters);
        $secondLetters = array_unique($secondLetters);

        preg_match_all('#\s(\w\w)\s#', $this->text, $matches);
        $twoCharWordsInText = array_unique($matches[1]);


        foreach ($twoCharWordsInText as $word) {
            $f = $word[0];
            $this->perms[$f] = array_intersect($this->perms[$f], $firstLetters);
            $s = $word[1];
            $this->perms[$s] = array_intersect($this->perms[$s], $secondLetters);
        }
    }

    private function acceptableCharacters()
    {
        return array_keys($this->polishFrequency);
    }
}