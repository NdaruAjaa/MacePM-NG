<?php

namespace XeonCh\Mace\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\world\particle\Particle;

class WindParticle implements Particle
{

    public function encode(Vector3 $pos): array
    {
        return [LevelEventPacket::standardParticle(ParticleIds::WIND_EXPLOSION, 0, $pos)];
    }
}
