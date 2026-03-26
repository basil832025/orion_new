<?php

// Нормализация названия турнира:
// если накопились двойные/тройные слеши перед кавычками,
// схлопываем их до одного (\\\" -> \"), но не убираем полностью.
if (isset($this->form['name'])) {
    $turnir_name = (string)$this->form['name'];

    for ($i = 0; $i < 5; $i++) {
        $normalized = preg_replace('/\\\\{2,}(?=["\'])/', '\\\\', $turnir_name);
        if ($normalized === $turnir_name) {
            break;
        }
        $turnir_name = $normalized;
    }

    $this->form['name'] = $turnir_name;
    $oQeury->addField('', 'name', $turnir_name);
}

$oQeury->update();

?>
