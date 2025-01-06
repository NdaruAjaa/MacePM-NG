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

namespace XeonCh\Mace\entity;

use pocketmine\entity\Living;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use XeonCh\Mace\particle\WindParticle;

class WindCharge extends Throwable
{
    public const WIND_CHARGE_PROJECTILE = "minecraft:wind_charge_projectile";

    public static function getNetworkTypeId(): string
    {
        return self::WIND_CHARGE_PROJECTILE;
    }

    protected function onHit(ProjectileHitEvent $event): void
    {
        $world = $this->getWorld();
        $world->addParticle($this->location, new WindParticle());
        $radius = 1.5;
        $boundingBox = new AxisAlignedBB(
            $this->location->x - $radius,
            $this->location->y - $radius,
            $this->location->z - $radius,
            $this->location->x + $radius,
            $this->location->y + $radius,
            $this->location->z + $radius
        );
        $nearbyEntities = $world->getNearbyEntities($boundingBox);
        foreach ($nearbyEntities as $entity) {
            if ($entity instanceof Living) {
                if ($entity->getId() !== $this->getOwningEntity()?->getId()) {
                    $entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_PROJECTILE, 1));
                }
                $this->applyKnockBack($entity);
            }
        }
        $x = $this->getPosition()->getX();
        $y = $this->getPosition()->getY();
        $z = $this->getPosition()->getZ();

        $nearbyP = $this->getOwningEntity()?->getWorld()->getNearbyEntities(new AxisAlignedBB($x - 20, $y - 20, $z - 20, $x + 20, $y + 20, $z + 20));
        foreach ($nearbyP as $near) {
            if ($near instanceof Player) {
                $near->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("wind_charge.burst", $x, $y, $z, 1.0, 1.0));
            }
        }
    }

    private function applyKnockBack(Living $entity): void
    {
        $direction = $entity->getPosition()->subtractVector($this->location);
        $direction->x = 0;
        $direction->z = 0;
        $knockBackVector = $direction->normalize()->multiply(2.5)->addVector(new Vector3(0, 1, 0));

        $entity->setMotion($knockBackVector);
    }
}
