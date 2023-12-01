currentBranch := $(shell git rev-parse --abbrev-ref HEAD)

testing:
	git branch -D testing
	git fetch origin testing:testing
	git checkout testing
	git merge $(currentBranch)
	git push origin testing:testing
	git checkout $(currentBranch)

rebase:
	git branch -D master
	git fetch origin master:master
	git branch --set-upstream-to=origin/master  master
	git checkout master
	git pull
	git checkout $(currentBranch)
	git rebase master
	git push origin $(currentBranch):$(currentBranch)

# 合并到开发分支
dev:
	# 开始合并当前分支到开发分支
	git branch -D $(branch)
	git fetch origin $(branch):$(branch)
	git branch --set-upstream-to=origin/$(branch) $(branch)
	git checkout $(branch)
	git merge $(currentBranch)
	git push origin $(branch):$(branch)
	# 开始合并开发分支到测试分支
	git branch -D testing
	git fetch origin testing:testing
	git checkout testing
	git merge $(branch)
	git push origin testing:testing
	git checkout $(currentBranch)

