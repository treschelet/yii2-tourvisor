<?php
/**
 * Created by Treschelet.
 * Date: 06.08.14
 */

namespace treschelet\tourvisor\models;

use yii\base\Model;

class SearchForm extends Model
{
    public $departure;
    public $country;
    public $datefrom;
    public $dateto;
    public $nightsfrom = 7;
    public $nightsto = 10;
    public $adults = 2;
    public $child = 0;
    public $childage1;
    public $childage2;
    public $childage3;
    public $stars;
    public $starsbetter = 1;
    public $meal;
    public $mealbetter = 1;
    public $rating;
    public $hotels;
    public $hoteltypes;
    public $pricetype = 0;
    public $regions;
    public $operators;
    public $pricefrom;
    public $priceto;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['departure', 'country'], 'required'],
            [['departure', 'country', 'stars', 'hotels', 'regions', 'operators', 'pricefrom', 'priceto'], 'integer'],
            [['nightsfrom', 'nightsto'], 'integer', 'min' => 2, 'max' => 29],
            ['adults', 'integer', 'min' => 0 , 'max' => 4],
            ['child', 'integer', 'min' => 0 , 'max' => 3],
            [['childage1', 'childage2', 'childage3'], 'integer', 'min'=> 0, 'max' => 14],
            [['starsbetter', 'mealbetter'], 'boolean'],
            ['pricetype', 'in', 'range' => [0, 1]],
            ['rating', 'in', 'range' => [0, 2, 3, 4, 5]],
            ['hoteltypes', 'in', 'range' => ['active', 'relax', 'family', 'health', 'city', 'beach', 'deluxe'], 'allowArray' => true],
            [['datefrom', 'dateto'], 'date', 'format' => 'd.m.Y'],
        ];
    }

    public function getPriceTypes()
    {
        return [
            0 => 'Цена за номер',
            1 => 'Цена за человека'
        ];
    }

    public function getHotelTypes()
    {
        return [
            'active' => 'Активный',
            'relax' => 'Спокойный',
            'family' => 'Семейный',
            'health' => 'Здоровье',
            'city' => 'Городской',
            'beach' => 'Пляжный',
            'deluxe' => 'Deluxe',
        ];
    }
} 