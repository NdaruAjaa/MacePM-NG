<?php

declare(strict_types=1);

namespace XeonCh\Mace\enchantments;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use  pocketmine\item\enchantment\Rarity;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\utils\RegistryTrait;

/**
 * @method static Enchantment BREACH()
 * @method static Enchantment DENSITY()
 * @method static Enchantment WIND_BURST()
 */
class MaceEnchantmentIds
{
    use RegistryTrait;

    /**
     * @return void
     */
    protected static function setup(): void
    {
        self::_registryRegister("BREACH", new Enchantment(KnownTranslationFactory::enchantment_heavy_weapon_breach(), Rarity::MYTHIC, ItemFlags::NONE, ItemFlags::NONE, 4));
        self::_registryRegister("DENSITY", new Enchantment(KnownTranslationFactory::enchantment_heavy_weapon_density(), Rarity::MYTHIC, ItemFlags::NONE, ItemFlags::NONE, 5));
        self::_registryRegister("WIND_BURST", new Enchantment(KnownTranslationFactory::enchantment_heavy_weapon_windburst(), Rarity::MYTHIC, ItemFlags::NONE, ItemFlags::NONE, 3));
    }
}