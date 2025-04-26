<?php

/*MIT License

Copyright (c) 2025 Jasson44

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.*/

declare(strict_types=1);

namespace XeonCh\Mace;

use pocketmine\block\Air;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\{mcpe\protocol\PlaySoundPacket};
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use XeonCh\Mace\item\Mace;

class EventListener implements Listener
{

    private array $playerFallDistance = [];

    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        $currentY = $event->getTo()->getY();
        $previousY = $event->getFrom()->getY();

        if ($currentY < $previousY) {
            if (!isset($this->playerFallDistance[$player->getName()])) {
                $this->playerFallDistance[$player->getName()] = 0;
            }
            $fallDistance = $this->playerFallDistance[$player->getName()] + ($previousY - $currentY);
            $this->playerFallDistance[$player->getName()] = $fallDistance;
        }
        if ($player->isOnGround()) {
            if (isset($this->playerFallDistance[$player->getName()])) {
               /* $fallDistance = $this->playerFallDistance[$player->getName()];
                if ($fallDistance > 1) {
                }*/
                unset($this->playerFallDistance[$player->getName()]);
            }
        }
    }

   public function MaceLogic(EntityDamageByEntityEvent $event): void
   {
       $damager = $event->getDamager();

       if (!$damager instanceof Player) {
            return;
       }

       $player = $damager;
       $item = $player->getInventory()->getItemInHand();

       if (!$item instanceof Mace) {
           return;
       }

       $playerName = $player->getName();
       if (!isset($this->playerFallDistance[$playerName])) {
           return;
       }

       $fallDistance = $this->playerFallDistance[$playerName];
       unset($this->playerFallDistance[$playerName]);
       
       if ($fallDistance > 1) {
            $bonusDamage = floor($fallDistance * 2) - 1;
            $densityEnchant = $item->getEnchantment(StringToEnchantmentParser::getInstance()->parse("density"));
            if ($densityEnchant !== null) {
                $densityLevel = $densityEnchant->getLevel();
                $densityMultiplier = match ($densityLevel) {
                    1 => 0.5,
                    2 => 1.0,
                    3 => 1.5,
                    4 => 2.0,
                    5 => 2.5,
                    default => 0,
                };

                $densityBonus = $fallDistance * $densityMultiplier;
                $bonusDamage += $densityBonus;
            }
            $newDamage = $event->getBaseDamage() + $bonusDamage;
            $event->setBaseDamage($newDamage);
        }

       $target = $event->getEntity();
        if ($target instanceof Player) {
            $enchant = $item->getEnchantment(StringToEnchantmentParser::getInstance()->parse("breach"));
            if ($enchant !== null) {
                $breachLevel = $enchant->getLevel();

                $armorInventory = $target->getArmorInventory();
                $armorPoints = 0;

                foreach ($armorInventory->getContents() as $armorItem) {
                    if (!$armorItem->isNull()) {
                        $armorPoints += $armorItem->getDefensePoints();
                    }
                }

                if ($armorPoints > 0) {
                    $reductionPercent = $armorPoints * 4;
                    $effectiveReduction = max(0, $reductionPercent - (15 * $breachLevel));

                    $finalDamageMultiplier = (100 - $effectiveReduction) / 100;
                    $newDamage = $event->getBaseDamage() * $finalDamageMultiplier;
                    $event->setBaseDamage($newDamage);
                }
            }
        }
       if ($fallDistance >= 3) {
           $targetPos = $event->getEntity()->getPosition();
           $world = $player->getWorld();
           $blockUnder = $world->getBlock($targetPos->subtract(0, 1, 0));
           $block = ($blockUnder instanceof Air) ? "grass" : $blockUnder->getName();
           $x = $targetPos->getX();
           $y = $targetPos->getY();
           $z = $targetPos->getZ();

           $maxHeight = 4.0;
           $step = 0.5;
           $offset = 1.5;

           for ($i = 0; $i <= $maxHeight; $i += $step) {
               $currentY = $y + $i;

               $positions = [
                   new Vector3($x + $offset, $currentY, $z),
                   new Vector3($x - $offset, $currentY, $z),
                   new Vector3($x, $currentY, $z + $offset),
                   new Vector3($x, $currentY, $z - $offset),
               ];

               foreach ($positions as $particlePos) {
                   $world->addParticle(
                       $particlePos,
                       new BlockBreakParticle(StringToItemParser::getInstance()->parse($block)->getBlock())
                   );
               }
           }

           $knockback = new Vector3($player->getMotion()->x, $player->getMotion()->y, $player->getMotion()->z);

           $knockback->x /= 2.0;
           $knockback->y /= 2.0;
           $knockback->z /= 2.0;

           $knockback->x -= ($player->getPosition()->x - $player->getPosition()->x) * 0.3;
           if (($enchant = $item->getEnchantment(StringToEnchantmentParser::getInstance()->parse("wind_burst"))) !== null) {
               switch ($enchant->getLevel()) {
                   case 1:
                       $knockback->y += 1.2;
                       break;
                       case 2:
                           $knockback->y += 2.0;
                           break;
                           case 3:
                               $knockback->y += 3.1;
                               break;
               }
           } else {
               $knockback->y += 0.7;
           }

           $knockback->z -= ($player->getPosition()->z - $player->getPosition()->z) * 0.3;

           $player->setMotion($knockback);

           $nearbyEntities = $world->getNearbyEntities(new AxisAlignedBB($x - 20, $y - 20, $z - 20, $x + 20, $y + 20, $z + 20));
           foreach ($nearbyEntities as $entity) {
               if ($entity instanceof Player) {
                   $entity->getNetworkSession()->sendDataPacket(PlaySoundPacket::create(
                       "mace.heavy_smash_ground",
                       $x,
                       $y,
                       $z,
                       1.0,
                       1.0
                   ));
               }
           }
       }
   }
}
