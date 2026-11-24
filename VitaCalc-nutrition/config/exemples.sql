USE vitacalc_nutrition;

START TRANSACTION;

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Alimentation flexitarienne', 'L’alimentation flexitarienne fait une place importante aux végétaux et laisse la possibilité de consommer occasionnellement de la viande ou du poisson.\n\nUne façon de la découvrir consiste à essayer un repas à base de légumineuses ou de céréales complètes.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeAlimentaire WHERE nomRegime = 'Alimentation flexitarienne');

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Alimentation végétarienne', 'L’alimentation végétarienne exclut la viande et le poisson. Selon les habitudes, elle peut inclure des œufs et des produits laitiers.\n\nLes lentilles, les haricots, le tofu et les pois chiches offrent différentes façons de varier les repas.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeAlimentaire WHERE nomRegime = 'Alimentation végétarienne');

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Régime méditerranéen', 'Cette façon de manger s’inspire des traditions culinaires méditerranéennes. Elle fait la part belle aux légumes, aux fruits, aux légumineuses, aux céréales et à l’huile d’olive.\n\nUn exemple de repas est une salade de pois chiches et de légumes de saison, accompagnée de pain complet.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeAlimentaire WHERE nomRegime = 'Régime méditerranéen');

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Varier les sources de protéines', 'Explorez les aliments que vous cuisinez moins souvent. Les pois chiches, les lentilles, le tofu ou les œufs peuvent apporter de la variété aux repas selon vos préférences.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifNutritionnel WHERE nomObjectif = 'Varier les sources de protéines');

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Boire de l’eau régulièrement', 'Gardez de l’eau à portée de main pendant la journée et pensez à en proposer à table. Une gourde peut servir de repère pour conserver cette habitude au travail ou en cours.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifNutritionnel WHERE nomObjectif = 'Boire de l’eau régulièrement');

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Diversifier son assiette', 'Essayez d’ajouter un fruit, un légume ou une céréale que vous utilisez rarement. Choisir un produit de saison est une façon simple de renouveler vos idées de repas.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifNutritionnel WHERE nomObjectif = 'Diversifier son assiette');

COMMIT;
