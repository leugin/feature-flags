<?php

namespace Leugin\FeatureFlags\Drivers\GrowthBook;

use Growthbook\Growthbook;
use Leugin\FeatureFlags\Contracts\FeatureFlagService;
use Leugin\FeatureFlags\Data\Dtos\User;
use Leugin\FeatureFlags\Exceptions\NotFoundException;

class GrowthBookDriver implements FeatureFlagService
{

    private $features;
    private $global;

    private $user;
    /**
     * @var int
     */
    private $maxAttempts;

    /**
     * @param string $url
     * @param int $maxAttempts
     */
    public function __construct(string  $url, int $maxAttempts = 3)
    {
        $this->maxAttempts = $maxAttempts;
        $this->features =  $this->loadFeatures($url);
        $this->global = $this->makeGrowthBook();

    }

    public function loadFeatures(string $url): array
    {
        $feature = null;
        $intent = 1;
         while (is_null($feature) && $intent <= $this->maxAttempts){
            $feature = $this->tryToConnect($url, $feature, $intent);
        }
        if (is_null($feature)){
            throw new NotFoundException("Features not found in the response object", 404);
        }

        return $feature;
    }

    /**
     * @param string $url
     * @param $feature
     * @param int $intent
     * @return array
     */
    public function tryToConnect(string $url, $feature, int &$intent): ?array
    {
        $apiResponse = $this->getResponse($url);
        if (!empty($apiResponse) && !empty($apiResponse["features"])) {
            $feature = $apiResponse["features"];
        }
        $intent++;
        return $feature;
    }
    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->global =  $this->makeGrowthBook($user);
    }

    public function show(string $featureFlagName): bool
    {
        return  $this->global->isOn($featureFlagName);
    }

    public function showForUser(string $featureFlagName, User $user): bool
    {
        return $this->makeGrowthBook($user)->isOn($featureFlagName);
    }

    /**
     * @param User|null $user
     * @return Growthbook
     */
    public function makeGrowthBook(?User  $user = null): Growthbook
    {
        $instance =  Growthbook::create()
            ->withFeatures($this->features)
        ;

        if ($user) {
            $instance->withAttributes($user->toArray());
        }
        return  $instance;
    }

    public function dump(): array
    {
        return  [
            'attributes' => $this->global->getAttributes(),
            'features' => $this->global->getFeatures(),
        ];
    }

    public function features(): array
    {
        $features = [];
        foreach ($this->global->getFeatures() as $k => $feature) {
            if (self::show($k))
                $features[] = $k;
        }
        return  $features;
    }

    /**
     * @param string $url
     * @return array
     */
    public function getResponse(string $url)
    {
        try
        {
            return json_decode(file_get_contents($url), true);
        } catch (\Exception $e) {
            throw new NotFoundException("Features not found in the response");
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getGlobal(): ?Growthbook
    {
        return $this->global;
    }
}