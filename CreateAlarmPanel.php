<?php

$alarmCategorySource = 14887;
$alarmCategoryVisu = 54132;

DeleteCategoriesAndLinks($alarmCategoryVisu);
CreateLinks($alarmCategorySource, $alarmCategoryVisu);

function DeleteCategoriesAndLinks(int $objectId, string $path = "")
{
    $childrenIds = IPS_GetChildrenIDs($objectId);

    foreach ($childrenIds as $key => $childId)
    {
        $child = IPS_GetObject($childId);

        $objectType = $child['ObjectType'];

        if ($objectType == 0) // Category
        {
            // Delete children
            DeleteCategoriesAndLinks($child['ObjectID'], $path . $child['ObjectName'] . "/");

            echo "Lösche Kategorie " . $path . $child['ObjectName'] . "\n";
            IPS_DeleteCategory($childId);
        }
        else if ($objectType == 6) // Link
        {
            echo "Lösche Link " . $path . $child['ObjectName'] . "\n";

            IPS_DeleteLink($childId);
        }
    }
}

function CreateLinks(int $sourceCategory, int $targetCategory, string $path = "")
{
    $childrenIds = IPS_GetChildrenIDs($sourceCategory);

    foreach ($childrenIds as $key => $childId)
    {
        $child = IPS_GetObject($childId);

        // print_r($child);

        $objectType = $child['ObjectType'];

        if ($objectType == 0) // Category
        {
            echo "Erstelle Kategorie " . $path . $child['ObjectName'] . "\n";

            $newCategoryId = IPS_CreateCategory();

            IPS_SetParent($newCategoryId, $targetCategory);
            IPS_SetName($newCategoryId, $child['ObjectName']);
            IPS_SetPosition($newCategoryId, $child["ObjectPosition"]);

            // Copy sub category
            CreateLinks($child['ObjectID'], $newCategoryId, $path . $child['ObjectName'] . "/");
        }
        else if ($objectType == 1) // Instance
        {
            foreach ($child['ChildrenIDs'] as $key => $instanceChildId)
            {
                $instanceChild = IPS_GetObject($instanceChildId);

                if ($instanceChild['ObjectType'] == 2 /* Variable */ && $instanceChild['ObjectIdent'] == "Value")
                {
                    echo "Erstelle Link " . $path . $child['ObjectName'] . "\n";

                    $linkId = IPS_CreateLink();

                    IPS_SetParent($linkId, $targetCategory);
                    IPS_SetName($linkId, $child['ObjectName']);
                    IPS_SetLinkTargetID($linkId, $instanceChildId);
                    IPS_SetPosition($linkId, $child["ObjectPosition"]);
                }
            }
        }
    }
}