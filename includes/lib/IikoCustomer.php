<?php

/**
 * Класс гостя
 */
class IikoCustomer {

	/**
	 * @var - Guid? Id. Для обновления информации о существующем госте должен быть задан номер телефона, или трек карты, или id.
	 * Для нового гостя поле id не задавать.
	 */
	public $id;

	/**
	 * @var - string. Имя
	 */
	public $name;

	/**
	 * @var - string. Телефонный номер. Для импорта должен быть задан номер телефона, или трек карты.
	 */
	public $phone;

	/** @var  string.	Трек карты. Для импорта должен быть задан номер телефона, или трек карты. */
	public $magnetCardTrack;

	/** @var - string Номер карты. */
	public $magnetCardNumber;

	/**
	 * @var - Date. День рождения
	 */
	public $birthday;

	/**
	 * @var - string. email
	 */
	public $email;

	/**
	 * @var -string. Отчество
	 */
	public $middleName;

	/**
	 * @var string. Фамилия
	 */
	public $surName;

	/**
	 * @var string. Пол: NotSpecified = 0, Male = 1, Female = 2
	 */
	public $sex;

	/**
	 * @var bool. Получает ли гость рассылки.
	 * @type bool
	 */
	public $shouldReceivePromoActionsInfo;

	/**
	 * @var - Guid. Id рекомендателя гостя. Null - не изменяет проставленного в анкете рекомендателя,
	 * пустой Guid (00000000-0000-0000-0000-000000000000) - удаляет рекомендателя у гостя.
	 * Иное значение - устанавливает рекомендателя.
	 */
	public $referrerId;

	/**
	 * @var string. Техническая инфрмация о госте (до 4000 символов)
	 */
	public $userData;

	/**
	 * @var - enum. Статус согласия на хранения и обработку персональных данных.
	 * 0 - Не задан, 1 - Есть согласие, 2 - Согласие отозвано ПД будут удалены через некоторое время.
	 */
	public $consentStatus;


	public function __construct( $id, $name, $phone, $magnetCardTrack, $magnetCardNumber, $birthday, $email, $middleName, $surName, $sex, $shouldReceivePromoActionsInfo, $referrerId, $userData, $consentStatus ) {

	}

	public function __set( $name, $value ) {
		// TODO: Implement __set() method.
	}

	public function __get( $name ) {
		// TODO: Implement __get() method.
	}

	public function __isset( $name ) {
		// TODO: Implement __isset() method.
	}

}