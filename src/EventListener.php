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

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\{mcpe\protocol\PlaySoundPacket,
    mcpe\protocol\SpawnParticleEffectPacket,
    mcpe\protocol\types\DimensionIds};
use pocketmine\player\Player;
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

        if ($damager instanceof Player) {
            $player = $damager;
            $item = $player->getInventory()->getItemInHand();

            if ($item instanceof Mace) {
                if (isset($this->playerFallDistance[$player->getName()])) {
                    $fallDistance = $this->playerFallDistance[$player->getName()];
                    $damage = 0;
                    if ($fallDistance > 2) {
                        $damage = 5 * ($fallDistance - 1); 
                    }
                    $newDamage = $event->getBaseDamage() + $damage;
                    $event->setBaseDamage($newDamage);
                    $impactPos = $event->getEntity()->getPosition();
                    $x = $impactPos->getX();
                    $y = $impactPos->getY();
                    $z = $impactPos->getZ();

                    $nearbyP = $player->getWorld()->getNearbyEntities(new AxisAlignedBB($x - 20, $y - 20, $z - 20, $x + 20, $y + 20, $z + 20));

                    $blockUnder = $player->getWorld()->getBlock($impactPos->subtract(0, 1, 0));
                    $block = ($blockUnder instanceof Air) ? "grass" : $blockUnder->getName();

                    $radiuses = [1, 2, 3];
                    foreach ($radiuses as $radius) {
                        for ($angle = 0; $angle < 360; $angle += 15) {
                            $particleX = $x + ($radius * cos(deg2rad($angle)));
                            $particleZ = $z + ($radius * sin(deg2rad($angle)));

                            $particleY = $y + 3 + rand(-1, 1);

                            $particlePosition = new Vector3($particleX, $particleY, $particleZ);
                            $player->getWorld()->addParticle(
                                $particlePosition,
                                new BlockBreakParticle(StringToItemParser::getInstance()->parse($block)->getBlock())
                            );

                            if ($radius > 1) {
                                $innerRadius = $radius - 0.5;
                                $particleX = $x + ($innerRadius * cos(deg2rad($angle)));
                                $particleZ = $z + ($innerRadius * sin(deg2rad($angle)));
                                $particlePosition = new Vector3($particleX, $particleY, $particleZ);
                                $player->getWorld()->addParticle(
                                    $particlePosition,
                                    new BlockBreakParticle(StringToItemParser::getInstance()->parse($block)->getBlock())
                                );
                            }
                        }
                    }
                    foreach ($nearbyP as $near) {
                        if ($near instanceof Player) {
                            $near->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mace.heavy_smash_ground", $x, $y, $z, 1.0, 1.0));
                        }
                    }
                    unset($this->playerFallDistance[$player->getName()]);
                }
            }
        }
    }
}
