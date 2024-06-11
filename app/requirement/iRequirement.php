<?php
namespace App\Requirement;
    
interface iRequirement {
    //public getUserStories(); - When User Story is implemented
    public function getID();
    public function getHeading();
    public function getDescription();
    public function getPrioirity();
    public function getComments();
    public function getDependantRequirements();
    public function getImpactedRequirements();
    public function getSubrequirements();
    // get versions?

    // public function linkUserStory(UserStory&);
    // public function unlinkUserStory(UserStory&);

    public function addComment($id, $author, $date, $content);
    public function removeComment(& $comment);
    public function appendComment(& $comment);
    public function clearComments();

    public function markRequirementToDependOn(& $requirement, $impactCoefficient, $isInitiator);
    public function changeImpactCoefficientOnDependantRequirement(& $requirement, $newDependancyCoefficient);
    public function unmarkRequirementToDependOn(& $requirement, $isInitiator);
    public function clearDependanciesWhichThisRequirementLiesUpon();

    public function addDependantRequirement(& $requirement, $impactCoefficient, $isInitiator);
    public function changeImpactCoefficientOnDependantOnThisRequirement(& $requirement, $newDependancyCoefficient);
    public function removeDependancyToRequirement(& $requirement, $isInitiator);
    public function clearDependanciesBasedOnThisRequirement();

    public function appendSubrequirement(& $requirement);
    public function removeSubrequirement(& $requirement);
    public function clearSubrequirements();
}
?>