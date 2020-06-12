#!/usr/bin/python3

# Usage: ./deploy-to-multiple-worktrees.py REPO_PATH WORKTREE_DEVELOPMENT_PATH WORKTREE_PRODUCTION_PATH

import sys
import os
import subprocess
import re

WORKTREE_REGEX = r"^worktree (.*)\nHEAD ([a-f0-9]*)\n(?:branch refs\/heads\/(.*)|detached)$"

if len(sys.argv) < 4:
    print("Usage: ./deploy-to-multiple-worktrees.py REPO_PATH WORKTREE_DEVELOPMENT_PATH WORKTREE_PRODUCTION_PATH")
    exit(1)

REPO = os.path.abspath(sys.argv[1])
WORKTREE_DEVELOPMENT = os.path.abspath(sys.argv[2])
WORKTREE_PRODUCTION = os.path.abspath(sys.argv[3])

class Worktree:
    def __init__(self, path, branch, revision, version):
        self.path = path
        self.branch = branch
        self.revision = revision
        self.version = version
        self.newRevision = None
        self.newVersion = None

def getDataForWorktrees():
    ret = subprocess.check_output(["git", "worktree", "list", "--porcelain"], cwd=REPO).decode().strip()
    blocks = ret.split("\n\n")

    worktrees = []

    for block in blocks:
        matches = re.search(WORKTREE_REGEX, block)

        if matches:
            path = matches.group(1)
            revision = matches.group(2)
            branch = matches.group(3)
            version = getVersion(revision)

            worktrees.append(Worktree(path, branch, revision, version))

    return worktrees

def findWorktree(path):
    for worktree in worktrees:
        if worktree.path == path:
            return worktree

    return None

def getVersion(branch):
    return subprocess.check_output(["git", "describe", "--tags", "--always", "--match", "Release_*", branch], cwd=REPO).decode().strip()

def getRevisionForRef(ref):
    return subprocess.check_output(["git", "rev-list", "-1", ref], cwd=REPO).decode().strip()

def getLatestReleaseTag():
    return subprocess.check_output(["git", "for-each-ref", "refs/tags/Release*", "--count=1", "--sort=-creatordate", "--format=%(refname:short)"], cwd=REPO).decode().strip()

def updateRepoFromRemote():
    subprocess.call(["git", "fetch", "origin", "--prune"], cwd=REPO)

def checkoutWorktree(worktreePath, ref):
    subprocess.call(["git", "checkout", "-f", ref], cwd=worktreePath)

def cleanWorktree(worktreePath):
    subprocess.call(["git", "clean", "-f", "-d"], cwd=worktreePath)

def updateAppInWorktree(worktreePath):
    subprocess.call([worktreePath + "/scripts/update.sh"], cwd=worktreePath)

def updateAppVersionInWorktree(worktreePath):
    subprocess.call([worktreePath + "/scripts/update-version.sh"], cwd=worktreePath)

worktrees = getDataForWorktrees()

updateRepoFromRemote()

print("Repo is updated from origin")

print("----------------------------------------------")
print("----------------------------------------------")

developmentWorktree = findWorktree(WORKTREE_DEVELOPMENT)

developmentWorktree.newRevision = getRevisionForRef(developmentWorktree.branch)
developmentWorktree.newVersion = getVersion(developmentWorktree.revision)

print("DEVELOPMENT (" + developmentWorktree.path + ") is on branch " + developmentWorktree.branch)
print(developmentWorktree.revision + " = " + developmentWorktree.branch + " (" + developmentWorktree.version + ")")
print(developmentWorktree.newRevision + " = origin/" + developmentWorktree.branch + " (" + developmentWorktree.newVersion + ")")

if developmentWorktree.revision != developmentWorktree.newRevision:
    print("-> DEVELOPMENT (" + developmentWorktree.path + ") will be UPDATED")
    print("----------------------------------------------")

    checkoutWorktree(developmentWorktree.path, developmentWorktree.branch)
    cleanWorktree(developmentWorktree.path)

    print(developmentWorktree.path + " is checked out to " + developmentWorktree.branch + " and cleaned")

    updateAppInWorktree(developmentWorktree.path)
    updateAppVersionInWorktree(developmentWorktree.path)

    print("MapGuesser is updated in " + developmentWorktree.path)
elif developmentWorktree.version != developmentWorktree.newVersion:
    print("-> DEVELOPMENT " + developmentWorktree.path + "'s version info will be UPDATED")

    updateAppVersionInWorktree(developmentWorktree.path)

    print("MapGuesser version is updated in " + developmentWorktree.path)
else:
    print("-> DEVELOPMENT (" + developmentWorktree.path + ") WON'T be updated")

print("----------------------------------------------")
print("----------------------------------------------")

productionWorktree = findWorktree(WORKTREE_PRODUCTION)

productionWorktree.newVersion = getLatestReleaseTag()
productionWorktree.newRevision = getRevisionForRef(productionWorktree.newVersion)

print("PRODUCTION (" + productionWorktree.path + ")")
print(productionWorktree.revision + " = " + productionWorktree.version)
print(productionWorktree.newRevision + " = " + productionWorktree.newVersion)

if productionWorktree.revision != productionWorktree.newRevision:
    print("-> PRODUCTION (" + productionWorktree.path + ") will be UPDATED")

    checkoutWorktree(productionWorktree.path, productionWorktree.newRevision)
    cleanWorktree(productionWorktree.path)

    print(productionWorktree.path + " is checked out to " + productionWorktree.newRevision + " and cleaned")

    updateAppInWorktree(productionWorktree.path)
    updateAppVersionInWorktree(productionWorktree.path)

    print("MapGuesser is updated in " + productionWorktree.path)
else:
    print("-> PRODUCTION (" + productionWorktree.path + ") WON'T be updated")

print("----------------------------------------------")
print("----------------------------------------------")
