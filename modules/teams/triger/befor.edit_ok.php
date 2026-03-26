<?php
// Автоматически устанавливаем is_team=1 при создании/редактировании команды
$form = poste('form');

// Гарантируем, что is_team всегда равен 1 для команд
if (!isset($form) || !is_array($form)) {
    $form = array();
}

// Устанавливаем is_team=1 всегда, независимо от того, что пришло в форме
$form['is_team'] = 1;

// Обновляем глобальные переменные
$_POST['form']['is_team'] = 1;
if (isset($_POST['form']) && is_array($_POST['form'])) {
    $_POST['form']['is_team'] = 1;
}

// КРИТИЧНО: Обновляем SystemClass::$aFormPost, так как FormSave использует его для получения данных формы
// SystemClass::$aFormPost устанавливается в конструкторе ДО выполнения триггера,
// поэтому нужно обновить его здесь, чтобы изменения попали в сохранение
if (class_exists('SystemClass')) {
    try {
        $reflection = new ReflectionClass('SystemClass');
        $property = $reflection->getProperty('aFormPost');
        $property->setAccessible(true);
        $currentForm = $property->getValue();
        if (!is_array($currentForm)) {
            $currentForm = array();
        }
        $currentForm['is_team'] = 1;
        $property->setValue(null, $currentForm);
    } catch (Exception $e) {
        // Если Reflection не работает, просто логируем ошибку
        // В этом случае сработает триггер after.edit_ok.php
    }
}

// Также устанавливаем в $this->aData, если он существует
if (isset($this) && isset($this->aData) && is_array($this->aData)) {
    $this->aData['is_team'] = 1;
}
