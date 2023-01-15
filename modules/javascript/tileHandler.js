class TileHandler {

    static currentDocument;
    static currentElement;
    static position;
    static elmIndex;
    static snapPoints;
    static newConnectionFrom;
    static newLinkImage;
    static lastLink;
    static instantiationY;

    constructor(curDocument) {
        TileHandler.currentDocument = curDocument;
        TileHandler.elmIndex = 1;
        TileHandler.instantiationY = 10;
        TileHandler.snapPoints = new Array();
        TileHandler.linked = new Array();
        TileHandler.lastLink = null;
        TileHandler.newLinkImage = TileHandler.insertLinkImage(0, 0);
        TileHandler.changeImgVisibility(false, TileHandler.newLinkImage);
    }

    static makeDraggable(element) {
        //add handlers
        element.onmousedown = TileHandler.mouseDown;
    }

    static mouseDown(event) {
        //save element
        let curElement = event.target;
        //stop if this is an input
        if(curElement.tagName === "INPUT") return;
        //if needed save sorrounding div instead of target
        while (!(curElement.classList.contains("draggable"))) {
            curElement = curElement.parentElement;
        }
        TileHandler.currentElement = curElement
        //prevent default behaviour
        event.preventDefault();
        //save position
        TileHandler.position = new Pos(event.clientX, event.clientY);
        //add function to follow cursor (on document level to ensure there is no move missed)
        TileHandler.currentDocument.onmousemove = TileHandler.mouseMove;
        //add function to stop following when mouse is released
        TileHandler.currentDocument.onmouseup = TileHandler.mouseUp;
    }

    static mouseMove(event) {
        //prevent default behaviour
        event.preventDefault();
        //calculate difference between elememts pos and mouse pos
        let xDiff = TileHandler.position.x - event.clientX;
        let yDiff = TileHandler.position.y - event.clientY;
        let currentOffset = new Pos(TileHandler.currentElement.offsetLeft, TileHandler.currentElement.offsetTop);
        //check if element is orginial element
        let elmId = TileHandler.currentElement.id;
        if(elmId.charAt(elmId.length-1) === '0') {
            TileHandler.addDuplicate();
        }
        //add equivalent offset to element
        TileHandler.currentElement.style.left = (currentOffset.x - xDiff) + "px";
        TileHandler.currentElement.style.top = (currentOffset.y - yDiff) + "px";
        //add equivalent offset to "connected" elements
        TileHandler.moveConnected(xDiff, yDiff, TileHandler.currentElement.id);
        //check if near snap point
        let nextSnap = TileHandler.inSnapRange();
        if(nextSnap != null) {
            //show linking image
            TileHandler.moveDiv(nextSnap.x - 18, nextSnap.y+parseInt(TileHandler.currentElement.clientHeight)/2 - 6, TileHandler.newLinkImage);
            TileHandler.changeImgVisibility(true, TileHandler.newLinkImage);
        }
        else {
            //delete connection if there was a link, wich is no more active
            if(TileHandler.lastLink != null && TileHandler.lastLink.next != null) {
                TileHandler.lastLink.next = null;
                TileHandler.lastLink.image.remove();
                TileHandler.lastLink.image = null;
            }
            TileHandler.changeImgVisibility(false, TileHandler.newLinkImage);
        }
        //log the currently connected snap point
        TileHandler.lastLink = nextSnap;
        //update position
        TileHandler.position = new Pos(event.clientX, event.clientY);
    }

    static mouseUp(event) {
        //delete duplicate if not dragged out of bottom field
        let elmId = TileHandler.currentElement.id;
        if(elmId.charAt(elmId.length-1) != '0') {
            if(TileHandler.currentElement.offsetTop > TileHandler.currentDocument.getElementById('barrier').offsetTop) {
                //delete from snap points list
                let toBeDeleted = -1;
                for(let i = 0; i < TileHandler.snapPoints.length; i++) {
                    if(TileHandler.snapPoints[i].id === TileHandler.currentElement.id) {
                        console.log("deleted: " + TileHandler.snapPoints[i].id);
                        toBeDeleted = i;
                        break;
                    }
                }
                if(toBeDeleted > -1) {
                    TileHandler.snapPoints.splice(toBeDeleted, 1);
                }
                TileHandler.currentElement.remove();
            }
            //otherwise make duplicate moveable on its own
            else {
                TileHandler.makeDraggable(TileHandler.currentElement);
                //register snap point of element
                var newSnapPos = TileHandler.insertSnapPos((parseInt(TileHandler.currentElement.offsetLeft+TileHandler.currentElement.clientWidth)), (parseInt(TileHandler.currentElement.offsetTop)), TileHandler.currentElement.id);
                //register link if still in range
                let possibleLink = TileHandler.inSnapRange();
                console.log(possibleLink);
                if(possibleLink != null && possibleLink.next === null) {
                    possibleLink.next = newSnapPos;
                    console.log("creating image");
                    let image = TileHandler.insertLinkImage(TileHandler.newLinkImage.offsetLeft,TileHandler.newLinkImage.offsetTop);
                    possibleLink.image = image;
                }

                //deactivate newLinkImage
                TileHandler.changeImgVisibility(false, TileHandler.newLinkImage);
            }
        }
        //unregister methods to prevent further cursor following
        TileHandler.currentDocument.onmousemove = null;
        TileHandler.currentDocument.onmouseup = null;
        //reset values
        TileHandler.lastLink = null;
    }

    static addDuplicate() {
        let originalElement = TileHandler.currentElement;
        let parent = originalElement.parentElement;
        //clone element and change id
        let clone = originalElement.cloneNode(true);
        clone.id = originalElement.id.slice(0, -1)+TileHandler.elmIndex;
        //increase index to prevent two nodes with same label
        TileHandler.elmIndex += 2;
        //insert new node into document
        parent.appendChild(clone);
        //update currentElement
        TileHandler.currentElement = clone;
    }

    static addDuplicateById(id) {
        let originalElement = TileHandler.currentDocument.getElementById(id);
        let parent = originalElement.parentElement;
        //clone element and change id
        let clone = originalElement.cloneNode(true);
        clone.id = originalElement.id.slice(0, -1)+TileHandler.elmIndex;
        //increase index to prevent two nodes with same label
        TileHandler.elmIndex += 2;
        //insert new node into document
        parent.appendChild(clone);
        //update currentElement
        TileHandler.currentElement = clone;
    }

    static moveConnected(xDiff, yDiff, id) {
        //check if there is a connection via snappoints
        let snapPoint = null;
        TileHandler.snapPoints.forEach((point) => {
            if(point.id === id) snapPoint = point;
        });
        if(snapPoint != null && snapPoint.next != null) {
            //move image
            let img = snapPoint.image;
            let imgPos = new Pos(img.offsetLeft, img.offsetTop);
            TileHandler.moveDiv(imgPos.x-xDiff, imgPos.y-yDiff, img);
            //move next
            //get by id
            let next = TileHandler.currentDocument.getElementById(snapPoint.next.id);
            let nextPos = new Pos(next.offsetLeft, next.offsetTop);
            TileHandler.moveDiv(nextPos.x-xDiff, nextPos.y-yDiff, next);
            //execute recursively
            TileHandler.moveConnected(xDiff, yDiff, snapPoint.next.id);
        }
    }

    static updateConnectedSnappoionts(xDiff, yDiff, id) {
        let toBeUpdated = null;
        for(let i = 0; i < TileHandler.snapPoints.length; i++) {
            if(TileHandler.snapPoints[i].id === id && TileHandler.snapPoints[i].next != null) {
                toBeUpdated = TileHandler.snapPoints[i].next;
                break;
            }
        }
        if(toBeUpdated != null) {
            //update position
            toBeUpdated.x = toBeUpdated.x - xDiff;
            toBeUpdated.y = toBeUpdated.y - yDiff;
            //execute recursively
            TileHandler.updateConnectedSnappoionts(xDiff, yDiff, toBeUpdated.id);
        }
    }

    static inSnapRange() {
        let result = null;

        TileHandler.snapPoints.forEach((sPoint) => {
            let xDistance = parseInt(sPoint.x) - parseInt(TileHandler.currentElement.style.left);
            let yDistance = parseInt(sPoint.y) - parseInt(TileHandler.currentElement.style.top);
            let sum = Math.pow(xDistance,2) + Math.pow(yDistance,2);
            let distance = Math.round(Math.sqrt(sum));
            if((distance < 18)&&(TileHandler.possibleConnection(sPoint.id, TileHandler.currentElement.id))) result = sPoint;
        });

        return result;
    }

    static insertSnapPos(x, y, id) {
        const newPos = new snapPos(x, y, id, null, null);
        let toBeDeleted = -1;
        for(let i = 0; i < TileHandler.snapPoints.length; i++) {
            if(TileHandler.snapPoints[i].id === newPos.id) {
                console.log("deleted: " + TileHandler.snapPoints[i].id);
                toBeDeleted = i;
                break;
            }
        }
        if(toBeDeleted > -1) {
            //if there is something attached to this element keep connection
            if(TileHandler.snapPoints[toBeDeleted].next != null) {
                newPos.next = TileHandler.snapPoints[toBeDeleted].next;
                newPos.image = TileHandler.snapPoints[toBeDeleted].image;
                //update position of connected snappoints
                let xDiff = TileHandler.snapPoints[toBeDeleted].x - newPos.x;
                let yDiff = TileHandler.snapPoints[toBeDeleted].y - newPos.y;
                TileHandler.updateConnectedSnappoionts(xDiff, yDiff, TileHandler.snapPoints[toBeDeleted].id);
            }
            TileHandler.snapPoints.splice(toBeDeleted, 1);
        }
        let length = TileHandler.snapPoints.push(newPos);
        return TileHandler.snapPoints[(length-1)];
    }

    static insertLinkImage(x, y) {
        return TileHandler.createImage(x, y, 42, 13, "assets/images/icons/linkIcon1.png",TileHandler.currentDocument.getElementById('conditionFieldBig'));
    }

    static createImage(x, y, width, height, source, parent) {
        var image = TileHandler.currentDocument.createElement('img');
        image.src = source;
        image.style.width =  width + "px";
        image.style.height = height + "px";
        var div = TileHandler.currentDocument.createElement('div');
        div.style.left = x + "px";
        div.style.top = y + "px";
        div.style.position = "absolute";
        div.appendChild(image);
        parent.appendChild(div);
        return div;
    }

    static moveDiv(x, y, div) {
        console.log("should move to: " +x+ ", "+y);
        div.style.left = x + "px";
        div.style.top = y + "px";
    }

    static changeImgVisibility(visible, reference) {
        if(visible === true) reference.children[0].style.visibility = "visible";
        else reference.children[0].style.visibility = "hidden";
    }

    static returnConstructs() {
        var resultChains = new Array();
        let points = TileHandler.snapPoints.slice(0);
        //as long as there are points left
        while(points.length > 0) {
            //define array for this construct
            var chain = new Array();
            //get first element which is the first of a construct
            let current = points[0];
            let isSecond = true;
            while (isSecond) {
                let first = null;
                for (let i = 0; i < points.length; i++) {
                    if(points[i].next === current) first = points[i];
                }
                if(first != null) {
                    current = first;
                }
                else {
                    isSecond = false;
                }
            }
            let next = current.next;
            let id = current.id.slice(0,3);
            //retrieve numbervalue if needed
            if(id === "num") {
                var val = TileHandler.currentDocument.getElementById(current.id).children[0].value;
                chain.push(val);
            }
            else {
                chain.push(current.id.slice(0,3));
            }
            //delete current from array
            let index = points.indexOf(current);
            points.splice(index, 1);
            while(next != null) {
                //get element
                current = next;
                next = current.next;
                let id = current.id.slice(0,3);
                //retrieve numbervalue if needed
                if(id === "num") {
                    var val = TileHandler.currentDocument.getElementById(current.id).children[0].value;
                    chain.push(val);
                }
                else {
                    chain.push(current.id.slice(0,3));
                }
                //delete current from array
                index = points.indexOf(current);
                console.log(index);
                points.splice(index, 1);
            }
            resultChains.push(chain);
        }
        console.log(resultChains);
        return resultChains;
    }

    static instantiateConstruct(bedingung) {
        let instantiationX = 10;
        let height = 0;
        let previousSegment = null;
        bedingung.forEach((segment) => {
            //duplicate base block of segment's type
            let baseId = segment+"0";
            //change if this is a number element
            if(!isNaN(baseId)){
                baseId = "num0";
                TileHandler.addDuplicateById(baseId);
                //set number
                TileHandler.currentElement.children[0].value = segment;
            }
            else {
                TileHandler.addDuplicateById(baseId);
            }
            
            //move to correct pos
            TileHandler.moveDiv(instantiationX, TileHandler.instantiationY, TileHandler.currentElement);
            //create snap point
            let newSnap = TileHandler.insertSnapPos((parseInt(TileHandler.currentElement.offsetLeft+TileHandler.currentElement.clientWidth)), (parseInt(TileHandler.currentElement.offsetTop)), TileHandler.currentElement.id);
            //connect to previous if needed
            if(previousSegment != null) {
                previousSegment.next = newSnap;
            }
            //create image
            if(previousSegment != null) {
                let img = TileHandler.insertLinkImage(previousSegment.x - 18, previousSegment.y+parseInt(TileHandler.currentElement.clientHeight)/2 - 6);
                previousSegment.image = img;
            }

            //update position and previousSegment
            height = Math.max(height, TileHandler.currentElement.clientHeight);
            instantiationX = instantiationX + TileHandler.currentElement.clientWidth + 7;
            previousSegment = newSnap;

            //make draggable
            TileHandler.makeDraggable(TileHandler.currentElement);
        });
        //ensure there is nothing left in current element
        TileHandler.currentElement = null;
        //update instantiationY
        TileHandler.instantiationY = TileHandler.instantiationY + height + 10;
    }

    //returns written out text for a shortform (or a number)
    static getText(shortform) {
        if(shortform === "min") {
            return "Mindestens";
        }
        else if(shortform === "max") {
            return "Maximal";
        }
        else if(shortform === "equ") {
            return "Genau";
        }
        else if(shortform === "cou") {
            return "Kurse";
        }
        else if(shortform === "les") {
            return "Wochenstunden";
        }
        else {
            return shortform;
        }
    }

    //gets class (start end mid...)
    static getClass(id) {
        let element = TileHandler.currentDocument.getElementById(id);
        const classes = element.classList;
        let index = 0;
        while(index < classes.length && classes[index] === "draggable") index++;
        return classes[index];
    }

    //checks if type allows connection
    static possibleConnection(idFirst, idSecond) {
        let firstClass = TileHandler.getClass(idFirst);
        let secondClass = TileHandler.getClass(idSecond);
        if((firstClass === "start" && secondClass === "mid")||(firstClass === "mid" && secondClass === "end")) return true;
        else return false;
    }
}

class Pos{
    constructor(x, y) {
        this.x = x;
        this.y = y;
    }
}

class snapPos {
    constructor(x, y, id, next, image) {
        this.x = x;
        this.y = y;
        this.id = id;
        this.next = next;
        this.image = image;
    }
}

class Link {
    constructor(from, to, image) {
        this.from = from;
        this.to = to;
        this.image = image;
    }
}